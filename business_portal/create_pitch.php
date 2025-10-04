<?php
// start session and check login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if user is logged in as a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    header("Location: ../login/login_signup.php");
    exit();
}

// include database connection
include '../sql/db.php';

if (!$mysql) {
    die("Database connection failed.");
}

// fetch tags from DB
$tagStmt = $mysql->prepare("SELECT * FROM Tag ORDER BY Name ASC");
$tagStmt->execute();
$tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessId = $_SESSION['userId'];
    $title = htmlspecialchars($_POST['title']);
    $elevator = htmlspecialchars($_POST['elevator']);
    $details = htmlspecialchars($_POST['details']);
    $target = $_POST['target'];
    $endDate = $_POST['end_date'];
    $profitShare = $_POST['profit_share'];
    $payoutFrequency = isset($_POST['payout_frequency']) ? $_POST['payout_frequency'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : 'draft';

    // insert new pitch
    $stmt = $mysql->prepare("
        INSERT INTO Pitch 
        (Title, ElevatorPitch, DetailedPitch, TargetAmount, WindowEndDate, ProfitSharePercentage, 
        PayoutFrequency, BusinessID, Status)
        VALUES 
        (:title, :elevator, :details, :target, :endDate, :profitShare, 
        :payoutFrequency, :businessId, :status)
    ");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':elevator', $elevator);
    $stmt->bindParam(':details', $details);
    $stmt->bindParam(':target', $target);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':profitShare', $profitShare);
    $stmt->bindParam(':payoutFrequency', $payoutFrequency);
    $stmt->bindParam(':businessId', $businessId);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    $pitchId = $mysql->lastInsertId(); // get inserted pitch ID

    // insert selected tags (max 5)
    if (isset($_POST['tags'])) {
        $selectedTags = array_slice($_POST['tags'], 0, 5);
        foreach ($selectedTags as $tagId) {
            $stmt = $mysql->prepare("INSERT INTO PitchTag (PitchID, TagID) VALUES (:pitchId, :tagId)");
            $stmt->bindParam(':pitchId', $pitchId);
            $stmt->bindParam(':tagId', $tagId);
            $stmt->execute();
        }
    }

    // insert investment tiers
    if (isset($_POST['tier_name'])) {
        $tierNames = $_POST['tier_name'];
        $tierMins = $_POST['tier_min'];
        $tierMaxs = $_POST['tier_max'];
        $tierMultipliers = $_POST['tier_multiplier'];

        for ($i = 0; $i < count($tierNames); $i++) {
            if (!empty($tierNames[$i])) {
                $stmt = $mysql->prepare("
                    INSERT INTO InvestmentTier 
                    (Name, Min, Max, Multiplier, PitchID, SharePercentage) 
                    VALUES (:name, :min, :max, :multiplier, :pitchId, :share)
                ");
                $stmt->bindParam(':name', $tierNames[$i]);
                $stmt->bindParam(':min', $tierMins[$i]);
                $stmt->bindParam(':max', $tierMaxs[$i]);
                $stmt->bindParam(':multiplier', $tierMultipliers[$i]);
                $stmt->bindParam(':pitchId', $pitchId);
                $stmt->bindParam(':share', $profitShare);
                $stmt->execute();
            }
        }
    }

    // redirect after submission
    if ($status === 'active') {
        header("Location: pitch_details.php?id=$pitchId&msg=submitted");
    } else {
        header("Location: business_dashboard.php?success=1");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Pitch</title>
    <link rel="stylesheet" href="create_pitch.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <!-- Form Section -->
    <main class="section">
        <h2>Create New Pitch</h2>

        <form class="pitch-form" method="POST" enctype="multipart/form-data">
            <!-- Product title -->
            <label for="title">Product Title</label>
            <input type="text" id="title" name="title" placeholder="Enter product title" required>

            <!-- Elevator pitch -->
            <label for="elevator">Elevator Pitch</label>
            <textarea id="elevator" name="elevator" rows="2" placeholder="Short summary of your idea"
                required></textarea>

            <!-- Detailed pitch -->
            <label for="details">Detailed Pitch</label>
            <textarea id="details" name="details" rows="5"
                placeholder="Explain your product/service, roadmap, customers, revenue potential..."
                required></textarea>

            <!-- Media upload -->
            <label for="media">Upload Images/Videos</label>
            <input type="file" id="media" name="media[]" multiple accept="image/*,video/*">

            <!-- Tags dropdown -->
            <div class="dropdown">
                <button type="button" class="dropbtn">Select Tags (max 5)</button>
                <div class="dropdown-content">
                    <?php foreach ($tags as $tag): ?>
                        <label class="checkbox">
                            <input type="checkbox" name="tags[]" value="<?php echo $tag['TagID']; ?>"
                                onchange="limitTags(this)">
                            <?php echo htmlspecialchars($tag['Name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <p class="note">Select up to 5 tags.</p>

            <!-- Target amount -->
            <label for="target">Target Investment Amount (£)</label>
            <input type="number" id="target" name="target" placeholder="e.g., 10000" required>

            <!-- Investment window end date -->
            <label for="end-date">Investment Window End Date</label>
            <input type="date" id="end-date" name="end_date" required>

            <!-- Profit share -->
            <label for="profit-share">Investor Profit Share %</label>
            <input type="number" id="profit-share" name="profit_share" min="1" max="100" placeholder="e.g., 20"
                required>

            <!-- Payout Frequency -->
            <label>Payout Frequency</label>
            <div class="payout-toggle">
                <button type="button" class="toggle-btn selected" data-value="Quarterly">Quarterly</button>
                <button type="button" class="toggle-btn" data-value="Annually">Annually</button>
            </div>
            <input type="hidden" name="payout_frequency" id="payout_frequency" value="Quarterly" required>

            <!-- Investment tiers -->
            <div class="tiers">
                <h3>Investment Tiers</h3>
                <div class="tier-row">
                    <input type="text" name="tier_name[]" placeholder="Tier Name (e.g. Bronze)">
                    <input type="number" name="tier_min[]" placeholder="Min (£)">
                    <input type="number" name="tier_max[]" placeholder="Max (£)">
                    <input type="number" step="0.1" name="tier_multiplier[]" placeholder="Multiplier (e.g. 1.2)">
                </div>
                <div class="tier-row">
                    <input type="text" name="tier_name[]" placeholder="Tier Name (e.g. Silver)">
                    <input type="number" name="tier_min[]" placeholder="Min (£)">
                    <input type="number" name="tier_max[]" placeholder="Max (£)">
                    <input type="number" step="0.1" name="tier_multiplier[]" placeholder="Multiplier (e.g. 1.5)">
                </div>
                <div class="tier-row">
                    <input type="text" name="tier_name[]" placeholder="Tier Name (e.g. Gold)">
                    <input type="number" name="tier_min[]" placeholder="Min (£)">
                    <input type="number" name="tier_max[]" placeholder="Max (£)">
                    <input type="number" step="0.1" name="tier_multiplier[]" placeholder="Multiplier (e.g. 1.7)">
                </div>
            </div>

            <!-- Buttons -->
            <div class="form-buttons">
                <button type="button" class="ai-btn">Run AI Analysis</button>
                <input type="hidden" name="status" id="status" value="draft">
                <button type="submit" class="draft-btn" onclick="document.getElementById('status').value='draft';">
                    Save as Draft
                </button>
                <button type="submit" class="submit-btn" onclick="document.getElementById('status').value='active';">
                    Submit Pitch
                </button>
            </div>
        </form>
    </main>

    <!-- AI analysis modal -->
    <div id="ai-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>AI Pitch Analysis</h3>
                <span class="close-btn">&times;</span>
            </div>

            <p class="rag-score">
                <strong>Score:</strong> <span id="rag">Analyzing...</span>
            </p>

            <div id="ai-feedback"></div>

            <div class="modal-buttons">
                <button id="apply-all" class="ai-btn">Apply All & Re-run</button>
                <button id="submit-anyway" class="submit-btn">Submit Anyway</button>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="create_pitch.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // dropdown toggle
        document.addEventListener('DOMContentLoaded', () => {
            const dropBtn = document.querySelector('.dropbtn');
            const dropdown = document.querySelector('.dropdown-content');

            dropBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });

            document.addEventListener('click', () => {
                dropdown.style.display = 'none';
            });
        });

        // limit tags to 5
        function limitTags(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="tags[]"]:checked');
            if (checkboxes.length > 5) {
                checkbox.checked = false;
                Toastify({
                    text: "Maximum of 5 tags allowed!",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            }
        }
    </script>
</body>

</html>