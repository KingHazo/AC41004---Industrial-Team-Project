<?php
// start session and check login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    header("Location: ../login/login_signup.php");
    exit();
}

// include database connection
include '../sql/db.php';

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

// fetch investment tiers
$stmt = $mysql->prepare("SELECT * FROM InvestmentTier WHERE PitchID=:pitchId ORDER BY Min ASC");
$stmt->bindParam(':pitchId', $pitchId);
$stmt->execute();
$tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// determine editable fields based on status
$status = strtolower($pitch['Status']);
$disableOtherFields = in_array($status, ['active', 'closed', 'funded']);

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $elevator = htmlspecialchars($_POST['elevator']);
    $details = htmlspecialchars($_POST['details']);
    $target = $_POST['target'];
    $endDate = $_POST['end_date'];
    $profitShare = $_POST['profit_share'];

    // update only editable fields
    $stmt = $mysql->prepare("
        UPDATE Pitch SET
            ElevatorPitch=:elevator,
            DetailedPitch=:details
            ".(!$disableOtherFields ? ", Title=:title, TargetAmount=:target, WindowEndDate=:endDate, ProfitSharePercentage=:profitShare" : "")."
        WHERE PitchID=:pitchId AND BusinessID=:businessId
    ");
    $stmt->bindParam(':elevator', $elevator);
    $stmt->bindParam(':details', $details);
    if (!$disableOtherFields) {
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':target', $target);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':profitShare', $profitShare);
    }
    $stmt->bindParam(':pitchId', $pitchId);
    $stmt->bindParam(':businessId', $_SESSION['userId']);
    $stmt->execute();

    // only update tiers if pitch is editable
    if (!$disableOtherFields) {
        // delete old tiers
        $stmt = $mysql->prepare("DELETE FROM InvestmentTier WHERE PitchID=:pitchId");
        $stmt->bindParam(':pitchId', $pitchId);
        $stmt->execute();

        // insert new tiers
        if (isset($_POST['tier_name'])) {
            $tierNames = $_POST['tier_name'];
            $tierMins = $_POST['tier_min'];
            $tierMaxs = $_POST['tier_max'];
            $tierMultipliers = $_POST['tier_multiplier'];

            for ($i = 0; $i < count($tierNames); $i++) {
                if (!empty($tierNames[$i])) {
                    $stmt = $mysql->prepare("INSERT INTO InvestmentTier (Name, Min, Max, Multiplier, PitchID, SharePercentage) VALUES (:name, :min, :max, :multiplier, :pitchId, :share)");
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
    }

    // redirect to dashboard with saved query param
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
<link rel="stylesheet" href="create_pitch.css">
<link rel="stylesheet" href="../footer.css">
<link rel="stylesheet" href="../navbar.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
#saveMessage {
  display: none;
  position: fixed;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  background: #27ae60;
  color: #fff;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  z-index: 1000;
}
</style>
</head>
<body>
<?php include '../navbar.php'; ?>

<main class="section">
<h2>Edit Pitch – <?php echo htmlspecialchars($pitch['Title']); ?></h2>
<form class="pitch-form" method="POST" enctype="multipart/form-data">
    <!-- Product title -->
    <label for="title">Product Title</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($pitch['Title']); ?>" 
           <?php echo $disableOtherFields ? 'readonly' : ''; ?> required>

    <!-- Elevator pitch -->
    <label for="elevator">Elevator Pitch</label>
    <textarea id="elevator" name="elevator" rows="2" required><?php echo htmlspecialchars($pitch['ElevatorPitch']); ?></textarea>

    <!-- Detailed pitch -->
    <label for="details">Detailed Pitch</label>
    <textarea id="details" name="details" rows="5" required><?php echo htmlspecialchars($pitch['DetailedPitch']); ?></textarea>

    <!-- media upload -->
    <label for="media">Upload Images/Videos</label>
    <input type="file" id="media" name="media[]" multiple accept="image/*,video/*" 
           <?php echo $disableOtherFields ? 'disabled' : ''; ?>>

    <!-- Target amount -->
    <label for="target">Target Investment Amount (£)</label>
    <input type="number" id="target" name="target" value="<?php echo $pitch['TargetAmount']; ?>" 
           <?php echo $disableOtherFields ? 'readonly' : ''; ?>>

    <!-- investment window end date -->
    <label for="end-date">Investment Window End Date</label>
    <input type="date" id="end-date" name="end_date" value="<?php echo $pitch['WindowEndDate']; ?>" 
           <?php echo $disableOtherFields ? 'readonly' : ''; ?>>

    <!-- Profit share -->
    <label for="profit-share">Investor Profit Share %</label>
    <input type="number" id="profit-share" name="profit_share" min="1" max="100" value="<?php echo $pitch['ProfitSharePercentage']; ?>" 
           <?php echo $disableOtherFields ? 'readonly' : ''; ?>>

    <!-- investment tiers -->
    <div class="tiers">
        <h3>Investment Tiers</h3>
        <?php foreach ($tiers as $tier): ?>
        <div class="tier-row">
            <input type="text" name="tier_name[]" value="<?php echo htmlspecialchars($tier['Name']); ?>" <?php echo $disableOtherFields ? 'readonly' : ''; ?>>
            <input type="number" name="tier_min[]" value="<?php echo $tier['Min']; ?>" <?php echo $disableOtherFields ? 'readonly' : ''; ?>>
            <input type="number" name="tier_max[]" value="<?php echo $tier['Max']; ?>" <?php echo $disableOtherFields ? 'readonly' : ''; ?>>
            <input type="number" step="0.1" name="tier_multiplier[]" value="<?php echo $tier['Multiplier']; ?>" <?php echo $disableOtherFields ? 'readonly' : ''; ?>>
        </div>
        <?php endforeach; ?>
        <?php if (!$disableOtherFields): ?>
        <div class="tier-row">
            <input type="text" name="tier_name[]" placeholder="Tier Name">
            <input type="number" name="tier_min[]" placeholder="Min (£)">
            <input type="number" name="tier_max[]" placeholder="Max (£)">
            <input type="number" step="0.1" name="tier_multiplier[]" placeholder="Multiplier">
        </div>
        <?php endif; ?>
    </div>

    <div class="form-buttons">
        <button type="button" class="ai-btn">Run AI Analysis</button>
        <button type="submit" class="submit-btn">Save Changes</button>
    </div>
</form>
</main>

<div id="saveMessage">Pitch saved successfully!</div>

<!-- AI analysis modal-->
<div id="ai-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>AI Pitch Analysis</h3>
        <p class="rag-score"><strong>Score:</strong> <span id="rag">Amber</span></p>
        <ul id="ai-feedback">
            <li>Your elevator pitch is clear.</li>
            <li>Add more details about market size.</li>
            <li>Consider simplifying revenue projections.</li>
        </ul>
        <div class="modal-buttons">
            <button id="reanalyse" class="ai-btn">Apply Feedback & Re-run</button>
            <button id="submit-anyway" class="submit-btn">Submit Anyway</button>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<script>
// show popup if redirected with saved=1
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('saved') === '1') {
    const msg = document.getElementById('saveMessage');
    msg.style.display = 'block';
    setTimeout(() => msg.style.display = 'none', 3000);
}
</script>
</body>
</html>
