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
    $title = htmlspecialchars($_POST['title']);
    $elevator = htmlspecialchars($_POST['elevator']);
    $details = htmlspecialchars($_POST['details']);
    $target = $_POST['target'];
    $endDate = $_POST['end_date'];
    $profitShare = $_POST['profit_share'];
    $payoutFrequency = $_POST['payout_frequency'];

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

    // update tags + tiers if editable
    if (!$disableOtherFields) {
        $mysql->prepare("DELETE FROM InvestmentTier WHERE PitchID=$pitchId")->execute();
        $mysql->prepare("DELETE FROM PitchTag WHERE PitchID=$pitchId")->execute();

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

        if (!empty($_POST['tags'])) {
            $selectedTags = array_slice($_POST['tags'], 0, 5);
            foreach ($selectedTags as $tagId) {
                $mysql->prepare("INSERT INTO PitchTag (PitchID, TagID) VALUES ($pitchId, $tagId)")->execute();
            }
        }
    }

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
        <label for="title">Product Title</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($pitch['Title']); ?>" <?php echo $disableOtherFields ? 'readonly' : ''; ?>>

        <label for="elevator">Elevator Pitch</label>
        <textarea id="elevator" name="elevator" rows="2"><?php echo htmlspecialchars($pitch['ElevatorPitch']); ?></textarea>

        <label for="details">Detailed Pitch</label>
        <textarea id="details" name="details" rows="5"><?php echo htmlspecialchars($pitch['DetailedPitch']); ?></textarea>

        <label for="media">Upload Images/Videos</label>
        <input type="file" id="media" name="media[]" multiple accept="image/*,video/*" <?php echo $disableOtherFields ? 'disabled' : ''; ?>>

        <!-- Tags -->
        <div class="dropdown">
            <button type="button" class="dropbtn">Select Tags (max 5)</button>
            <div class="dropdown-content">
                <?php foreach ($allTags as $tag): ?>
                    <label>
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['TagID']; ?>"
                            <?php echo in_array($tag['TagID'], $selectedTagsIds) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($tag['Name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <label for="target">Target Investment (£)</label>
        <input type="number" id="target" name="target" value="<?php echo $pitch['TargetAmount']; ?>">

        <label for="end-date">Investment Window End Date</label>
        <input type="date" id="end-date" name="end_date" value="<?php echo $pitch['WindowEndDate']; ?>">

        <label for="profit-share">Investor Profit Share %</label>
        <input type="number" id="profit-share" name="profit_share" min="1" max="100" value="<?php echo $pitch['ProfitSharePercentage']; ?>">

        <div class="tiers">
            <h3>Investment Tiers</h3>
            <?php foreach ($tiers as $tier): ?>
                <div class="tier-row">
                    <input type="text" name="tier_name[]" value="<?php echo htmlspecialchars($tier['Name']); ?>">
                    <input type="number" name="tier_min[]" value="<?php echo $tier['Min']; ?>">
                    <input type="number" name="tier_max[]" value="<?php echo $tier['Max']; ?>">
                    <input type="number" step="0.1" name="tier_multiplier[]" value="<?php echo $tier['Multiplier']; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-buttons">
            <button type="button" class="ai-btn">Run AI Analysis</button>
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

</body>
</html>
