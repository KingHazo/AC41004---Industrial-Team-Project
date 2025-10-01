<?php
// start the session to check login
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// make sure user is logged in and is a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
  header("Location: ../login/login_signup.php");
  exit();
}

// include database connection
include '../sql/db.php';

// get pitch ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  die("Pitch ID is missing.");
}

$pitchId = (int) $_GET['id'];

// fetch pitch from DB
$stmt = $mysql->prepare("SELECT * FROM Pitch WHERE PitchID = :pitchId AND BusinessID = :businessId");
$stmt->bindParam(':pitchId', $pitchId);
$stmt->bindParam(':businessId', $_SESSION['userId']);
$stmt->execute();
$pitch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pitch) {
  die("Pitch not found or you do not have permission to view it.");
}

// calculate progress
$progress = $pitch['TargetAmount'] > 0 ? ($pitch['CurrentAmount'] / $pitch['TargetAmount']) * 100 : 0;

// determine status
$status = "draft";
$disableEdit = false;
$disableProfit = false;
$now = date("Y-m-d");
if ($pitch['WindowEndDate'] && $now > $pitch['WindowEndDate']) {
  $status = "closed";
  $disableEdit = true;
} elseif ($pitch['CurrentAmount'] > 0) {
  $status = "active";
}

// fetch investment tiers for this pitch
$tierStmt = $mysql->prepare("SELECT * FROM InvestmentTier WHERE PitchID = :pitchId ORDER BY Min ASC");
$tierStmt->bindParam(':pitchId', $pitchId);
$tierStmt->execute();
$tiers = $tierStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pitch Details</title>
  <link rel="stylesheet" href="pitch_details.css">
  <link rel="stylesheet" href="business_navbar.css">
  <link rel="stylesheet" href="../footer.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php include '../navbar.php'; ?>


  <!-- Pitch Details Section -->
  <main class="section">
    <div class="pitch-card">
      <h2><?php echo htmlspecialchars($pitch['Title']); ?></h2>
      <p class="status <?php echo $status; ?>">Status: <?php echo ucfirst($status); ?></p>

      <h3>Elevator Pitch</h3>
      <p><?php echo nl2br(htmlspecialchars($pitch['ElevatorPitch'])); ?></p>

      <h3>Detailed Pitch</h3>
      <p><?php echo nl2br(htmlspecialchars($pitch['DetailedPitch'])); ?></p>

      <h3>Funding Progress</h3>
      <div class="progress-container">
        <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
          £<?php echo number_format($pitch['CurrentAmount'], 2); ?> /
          £<?php echo number_format($pitch['TargetAmount'], 2); ?>
        </div>
      </div>
      <p><strong>Funding Window End:</strong> <?php echo htmlspecialchars($pitch['WindowEndDate']); ?></p>

      <h3>Investor Profit Share</h3>
      <p><strong><?php echo htmlspecialchars($pitch['ProfitSharePercentage']); ?>%</strong></p>

      <h3>Investment Tiers</h3>
      <?php if ($tiers): ?>
        <table class="tiers-table">
          <thead>
            <tr>
              <th>Tier Name</th>
              <th>Min (£)</th>
              <th>Max (£)</th>
              <th>Share (%)</th>
              <th>Multiplier</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tiers as $tier): ?>
              <tr>
                <td><?php echo htmlspecialchars($tier['Name']); ?></td>
                <td><?php echo number_format($tier['Min'], 2); ?></td>
                <td><?php echo $tier['Max'] > 0 ? number_format($tier['Max'], 2) : '-'; ?></td>
                <td><?php echo htmlspecialchars($tier['SharePercentage']); ?></td>
                <td><?php echo htmlspecialchars($tier['Multiplier']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No investment tiers defined for this pitch yet.</p>
      <?php endif; ?>

      <div class="card-buttons">
        <a class="edit-btn" href="edit_pitch.php?id=<?php echo $pitch['PitchID']; ?>" <?php echo $disableEdit ? "disabled" : ""; ?>>Edit Pitch</a>
        <button class="profit-btn" <?php echo $disableProfit ? "disabled" : ""; ?>>Declare Profit</button>
      </div>
    </div>
  </main>
  <?php include '../footer.php'; ?>
  <script src="pitch_details.js"></script>
</body>

</html>