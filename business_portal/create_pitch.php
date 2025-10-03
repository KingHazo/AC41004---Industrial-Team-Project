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

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $businessId = $_SESSION['userId'];
  $title = htmlspecialchars($_POST['title']);
  $elevator = htmlspecialchars($_POST['elevator']);
  $details = htmlspecialchars($_POST['details']);
  $target = $_POST['target'];
  $endDate = $_POST['end_date'];
  $profitShare = $_POST['profit_share'];

  // insert pitch
  $stmt = $mysql->prepare("INSERT INTO Pitch (Title, ElevatorPitch, DetailedPitch, TargetAmount, WindowEndDate, ProfitSharePercentage, BusinessID) VALUES (:title, :elevator, :details, :target, :endDate, :profitShare, :businessId)");
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':elevator', $elevator);
  $stmt->bindParam(':details', $details);
  $stmt->bindParam(':target', $target);
  $stmt->bindParam(':endDate', $endDate);
  $stmt->bindParam(':profitShare', $profitShare);
  $stmt->bindParam(':businessId', $businessId);
  $stmt->execute();

  $pitchId = $mysql->lastInsertId(); // get the inserted PitchID

  // insert investment tiers
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
        $stmt->bindParam(':share', $profitShare); // assuming same profit share
        $stmt->execute();
      }
    }
  }

  // redirect after success
  header("Location: business_dashboard.php?success=1");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Pitch</title>
  <link rel="stylesheet" href="create_pitch.css">
  <link rel="stylesheet" href="../footer.css">
  <link rel="stylesheet" href="../navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      <textarea id="elevator" name="elevator" rows="2" placeholder="Short summary of your idea" required></textarea>

      <!-- Detailed pitch -->
      <label for="details">Detailed Pitch</label>
      <textarea id="details" name="details" rows="5"
        placeholder="Explain your product/service, roadmap, customers, revenue potential..." required></textarea>

      <!-- media upload -->
      <label for="media">Upload Images/Videos</label>
      <input type="file" id="media" name="media[]" multiple accept="image/*,video/*">

      <!-- Target amount -->
      <label for="target">Target Investment Amount (£)</label>
      <input type="number" id="target" name="target" placeholder="e.g., 10000" required>

      <!-- investment window end date -->
      <label for="end-date">Investment Window End Date</label>
      <input type="date" id="end-date" name="end_date" required>

      <!-- Profit share -->
      <label for="profit-share">Investor Profit Share %</label>
      <input type="number" id="profit-share" name="profit_share" min="1" max="100" placeholder="e.g., 20" required>

      <!-- investment tiers -->
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

      <!-- buttons -->
      <div class="form-buttons">
        <button type="button" class="ai-btn">Run AI Analysis</button>
        <button type="submit" class="submit-btn">Submit Pitch</button>
      </div>
    </form>
  </main>

  <!-- ai analysis -->
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
  <script src="create_pitch.js"></script>
</body>

</html>