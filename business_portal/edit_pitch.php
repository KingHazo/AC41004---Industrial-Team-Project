<?php
// start session and check login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    header("Location: ../login/login_signup.php");
    exit();
}

include '../sql/db.php';

if (!$mysql) {
    die("Database connection failed.");
}

// Include GCP
require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

// Initialize GCP Storage using JSON file in root
try {
    $storage = new StorageClient([
        'projectId' => 'fundify-474122',
        'keyFilePath' => __DIR__ . '/../fundify-474122.json' // path to JSON credentials
    ]);

    $bucket = $storage->bucket('fundify-media-bucket');
} catch (Exception $e) {
    error_log("GCP init error: " . $e->getMessage());
    die("Could not initialize Google Cloud Storage");
}

ini_set('post_max_size', '500M');
ini_set('upload_max_filesize', '100M');
ini_set('memory_limit', '300M');

// get PitchID from GET
$pitchId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pitchId) die("Pitch ID missing.");

// fetch pitch data
$stmt = $mysql->prepare("SELECT * FROM Pitch WHERE PitchID=:pitchId AND BusinessID=:businessId");
$stmt->bindParam(':pitchId', $pitchId);
$stmt->bindParam(':businessId', $_SESSION['userId']);
$stmt->execute();
$pitch = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pitch) die("Pitch not found or you do not have permission.");

// fetch tags + tiers
$allTagsStmt = $mysql->prepare("SELECT * FROM Tag ORDER BY Name ASC");
$allTagsStmt->execute();
$allTags = $allTagsStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedTagStmt = $mysql->prepare("SELECT TagID FROM PitchTag WHERE PitchID=:pitchId");
$selectedTagStmt->bindParam(':pitchId', $pitchId);
$selectedTagStmt->execute();
$selectedTagsIds = $selectedTagStmt->fetchAll(PDO::FETCH_COLUMN);

$tierStmt = $mysql->prepare("SELECT * FROM InvestmentTier WHERE PitchID=:pitchId ORDER BY Min ASC");
$tierStmt->bindParam(':pitchId', $pitchId);
$tierStmt->execute();
$tiers = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

// determine editability
$status = strtolower($pitch['Status']);
$disableOtherFields = in_array($status, ['active', 'closed', 'funded']);

// handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize form inputs
    $title = htmlspecialchars($_POST['title']);
    $elevator = htmlspecialchars($_POST['elevator']);
    $details = htmlspecialchars($_POST['details']);
    $target = $_POST['target'];
    $endDate = $_POST['end_date'];
    $profitShare = $_POST['profit_share'];
    $payoutFrequency = $_POST['payout_frequency'];

    // Update Pitch table
    $stmt = $mysql->prepare("
        UPDATE Pitch SET
        ElevatorPitch=:elevator,
        DetailedPitch=:details
        " . (!$disableOtherFields ? ", Title=:title, TargetAmount=:target, WindowEndDate=:endDate, ProfitSharePercentage=:profitShare, PayoutFrequency=:payoutFrequency" : "") . "
        WHERE PitchID=:pitchId AND BusinessID=:businessId
    ");
    $stmt->bindParam(':elevator', $elevator);
    $stmt->bindParam(':details', $details);
    if (!$disableOtherFields) {
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':target', $target);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':profitShare', $profitShare);
        $stmt->bindParam(':payoutFrequency', $payoutFrequency);
    }
    $stmt->bindParam(':pitchId', $pitchId);
    $stmt->bindParam(':businessId', $_SESSION['userId']);
    $stmt->execute();


    // Handle media uploads (only if pitch is editable)
    if (!$disableOtherFields && !empty($_FILES['media']['name'][0])) {

        $maxFiles = 5;
        $maxTotalSize = 200 * 1024 * 1024; 
        $maxFileSize = 100 * 1024 * 1024;  

        $fileCount = count($_FILES['media']['name']);
        $totalSize = array_sum($_FILES['media']['size']);

        // Validate number of files
        if ($fileCount > $maxFiles) {
            die("<script>alert('You can upload a maximum of 5 files.'); window.history.back();</script>");
        }

        // Validate total size
        if ($totalSize > $maxTotalSize) {
            die("<script>alert('Total upload size exceeds 200MB. Please reduce file sizes.'); window.history.back();</script>");
        }

        // Loop through each uploaded file
        foreach ($_FILES['media']['tmp_name'] as $index => $tmpName) {
            $error = $_FILES['media']['error'][$index];
            $size = $_FILES['media']['size'][$index];

            if ($error !== UPLOAD_ERR_OK) {
                error_log("Upload error index $index: $error");
                continue;
            }

            if ($size > $maxFileSize) {
                error_log("File too large: {$_FILES['media']['name'][$index]}");
                continue;
            }

            $originalName = basename($_FILES['media']['name'][$index]);
            $fileName = uniqid() . '_' . $originalName;

            try {
                // Upload to GCP bucket
                $object = $bucket->upload(fopen($tmpName, 'r'), ['name' => $fileName]);
                $publicUrl = "https://storage.googleapis.com/{$bucket->name()}/{$fileName}";
                error_log("✅ Uploaded to GCP: $fileName");

                // Insert media record into database
                $stmt = $mysql->prepare("INSERT INTO Media (FilePath, PitchID) VALUES (:filePath, :pitchId)");
                $stmt->execute([
                    ':filePath' => $publicUrl,
                    ':pitchId' => $pitchId
                ]);

            } catch (Exception $e) {
                error_log("GCP upload error: " . $e->getMessage());
            }
        }
    }

    // update tags and tiers if editable

    if (!$disableOtherFields) {
        // Delete existing tiers and tags
        $stmt = $mysql->prepare("DELETE FROM InvestmentTier WHERE PitchID=:pitchId");
        $stmt->execute([':pitchId' => $pitchId]);

        $stmt = $mysql->prepare("DELETE FROM PitchTag WHERE PitchID=:pitchId");
        $stmt->execute([':pitchId' => $pitchId]);

        // Add updated tiers
        if (!empty($_POST['tier_name'])) {
            foreach ($_POST['tier_name'] as $i => $name) {
                if (!empty($name)) {
                    $stmt = $mysql->prepare("INSERT INTO InvestmentTier (Name, Min, Max, Multiplier, PitchID, SharePercentage)
                        VALUES (:name, :min, :max, :mult, :pitchId, :share)");
                    $stmt->execute([
                        ':name' => $name,
                        ':min' => $_POST['tier_min'][$i],
                        ':max' => $_POST['tier_max'][$i],
                        ':mult' => $_POST['tier_multiplier'][$i],
                        ':pitchId' => $pitchId,
                        ':share' => $profitShare
                    ]);
                }
            }
        }

        // Add updated tags (limit to 5)
        if (!empty($_POST['tags'])) {
            $selectedTags = array_slice($_POST['tags'], 0, 5);
            foreach ($selectedTags as $tagId) {
                $stmt = $mysql->prepare("INSERT INTO PitchTag (PitchID, TagID) VALUES (:pitchId, :tagId)");
                $stmt->execute([
                    ':pitchId' => $pitchId,
                    ':tagId' => $tagId
                ]);
            }
        }
    }

    // Redirect back to dashboard after save
    header("Location: business_dashboard.php?saved=1");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pitch</title>
    <link rel="stylesheet" href="create_pitch.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <main class="section">
        <h2>Edit Pitch – <?php echo htmlspecialchars($pitch['Title']); ?></h2>
        <form class="pitch-form" method="POST" enctype="multipart/form-data">

            <!-- Display Status -->
            <p class="status <?php echo $status; ?>">Status: <?php echo ucfirst($status); ?></p>

            <label for="title">Product Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($pitch['Title']); ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>


            <label for="elevator">Elevator Pitch</label>
            <textarea id="elevator" name="elevator" rows="2"><?php echo htmlspecialchars($pitch['ElevatorPitch']); ?></textarea>

            <label for="details">Detailed Pitch</label>
            <textarea id="details" name="details" rows="5"><?php echo htmlspecialchars($pitch['DetailedPitch']); ?></textarea>

            <label for="media">Upload Images/Videos</label>
            <input type="file" id="media" name="media[]" multiple accept="image/*,video/*" <?php echo $disableOtherFields ? 'disabled' : ''; ?>>

            <!-- Tags -->

            <h3>Tags</h3>
            <?php if ($status === 'draft'): ?>
                <div class="dropdown">
                    <button type="button" class="dropbtn">Select Tags (max 5)</button>
                    <div class="dropdown-content">
                        <?php foreach ($allTags as $tag): ?>
                            <label class="checkbox">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['TagID']; ?>"
                                    <?php echo in_array($tag['TagID'], $selectedTagsIds) ? 'checked' : ''; ?>
                                    onchange="limitTags(this)">
                                <?php echo htmlspecialchars($tag['Name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="note">Select up to 5 tags.</p>
            <?php else: ?>
                <?php if ($selectedTagsIds): ?>
                    <div class="tags-container">
                        <?php
                        foreach ($allTags as $tag) {
                            if (in_array($tag['TagID'], $selectedTagsIds)) {
                                echo '<span class="tag">' . htmlspecialchars($tag['Name']) . '</span> ';
                            }
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <p>No tags assigned for this pitch.</p>
                <?php endif; ?>
            <?php endif; ?>


            <label for="target">Target Investment (£)</label>
            <input type="number" id="target" name="target" value="<?php echo $pitch['TargetAmount']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>

            <label for="end-date">Investment Window End Date</label>
            <input type="date" id="end-date" name="end_date" value="<?php echo $pitch['WindowEndDate']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>

            <label for="profit-share">Investor Profit Share %</label>
            <input type="number" id="profit-share" name="profit_share" min="1" max="100" value="<?php echo $pitch['ProfitSharePercentage']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>

            <?php
            $payout = $pitch['PayoutFrequency'] ?? 'Quarterly'; // fallback
            ?>
            <label>Payout Frequency</label>
            <div class="payout-toggle">
                <button type="button" class="toggle-btn <?php echo $payout === 'Quarterly' ? 'active' : ''; ?>"
                    data-value="Quarterly" <?php echo $status !== 'draft' ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''; ?>>Quarterly</button>
                <button type="button" class="toggle-btn <?php echo $payout === 'Annually' ? 'active' : ''; ?>"
                    data-value="Annually" <?php echo $status !== 'draft' ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''; ?>>Annually</button>
            </div>
            <input type="hidden" name="payout_frequency" id="payout_frequency" value="<?php echo $payout; ?>" required>


            <div class="tiers">
                <h3>Investment Tiers</h3>
                <?php foreach ($tiers as $tier): ?>
                    <div class="tier-row">
                        <input type="text" name="tier_name[]" value="<?php echo htmlspecialchars($tier['Name']); ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>
                        <input type="number" name="tier_min[]" value="<?php echo $tier['Min']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>
                        <input type="number" name="tier_max[]" value="<?php echo $tier['Max']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>
                        <input type="number" step="0.1" name="tier_multiplier[]" value="<?php echo $tier['Multiplier']; ?>" <?php echo $disableOtherFields ? 'readonly style="background:#eee;cursor:not-allowed;"' : ''; ?>>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-buttons">
                <?php if ($status === 'draft'): ?>
                    <button type="button" class="ai-btn">Run AI Analysis</button>
                <?php endif; ?>
                <button type="submit" class="submit-btn">Save Changes</button>
            </div>

        </form>
    </main>

    <!-- AI Modal -->
    <div id="ai-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>AI Pitch Analysis</h3>
                <span class="close-btn">&times;</span>
            </div>

            <p class="rag-score"><strong>Score:</strong> <span id="rag">Analyzing...</span></p>
            <div id="ai-feedback"></div>
            <div class="modal-buttons">
                <button id="apply-all" class="ai-btn">Apply All & Re-run</button>
                <button id="submit-anyway" class="submit-btn">Submit Anyway</button>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="create_pitch.js?v=<?php echo time(); ?>"></script>
    <script src="edit_pitch.js?v=<?php echo time(); ?>"></script>

</body>

</html>