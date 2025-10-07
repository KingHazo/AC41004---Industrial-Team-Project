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

if (!$mysql) {
  die("Database connection failed.");
}


// get pitch ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  die("Pitch ID is missing.");
}

$pitchId = (int) $_GET['id'];

// handle save using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json'); // <-- important!

  $data = json_decode(file_get_contents('php://input'), true);

  if (!$data || !isset($_SESSION['userId'])) {
    echo json_encode(['success' => false]);
    exit; // stop HTML rendering
  }

  $elevatorPitch = $data['elevatorPitch'];
  $detailedPitch = $data['detailedPitch'];

  $stmt = $mysql->prepare("
        UPDATE Pitch 
        SET ElevatorPitch = :elevatorPitch, DetailedPitch = :detailedPitch 
        WHERE PitchID = :pitchId AND BusinessID = :businessId
    ");
  $stmt->bindParam(':elevatorPitch', $elevatorPitch);
  $stmt->bindParam(':detailedPitch', $detailedPitch);
  $stmt->bindParam(':pitchId', $pitchId);
  $stmt->bindParam(':businessId', $_SESSION['userId']);
  $success = $stmt->execute();

  echo json_encode(['success' => $success]);
  exit; // <- critical! stops PHP from outputting the rest of the page
}



// fetch pitch from DB
$stmt = $mysql->prepare("SELECT * FROM Pitch WHERE PitchID = :pitchId AND BusinessID = :businessId");
$stmt->bindParam(':pitchId', $pitchId);
$stmt->bindParam(':businessId', $_SESSION['userId']);
$stmt->execute();
$pitch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pitch) {
  die("Pitch not found or you do not have permission to view it.");
}

// fetch media files for this pitch
$mediaStmt = $mysql->prepare("SELECT FilePath FROM Media WHERE PitchID = :pitchId ORDER BY MediaID ASC");
$mediaStmt->bindParam(':pitchId', $pitchId);
$mediaStmt->execute();
$mediaFiles = $mediaStmt->fetchAll(PDO::FETCH_COLUMN); // get array of URLs


// fetch tags for this pitch
$tagStmt = $mysql->prepare("
    SELECT t.Name 
    FROM Tag t
    INNER JOIN PitchTag pt ON t.TagID = pt.TagID
    WHERE pt.PitchID = :pitchId
");
$tagStmt->bindParam(':pitchId', $pitchId);
$tagStmt->execute();
$tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN); // get an array of tag names

$status = $pitch['Status'];
$payoutFrequency = $pitch['PayoutFrequency'];


$disableEdit = !in_array($status, ['active', 'draft']);

// disable edit if pitch is funded or closed
$disableEdit = ($status === 'funded' || $status === 'closed');

// calculate progress
$progress = $pitch['TargetAmount'] > 0 ? ($pitch['CurrentAmount'] / $pitch['TargetAmount']) * 100 : 0;

// determine status
$status = $pitch['Status']; // <-- take real status from DB
$disableEdit = false;
$disableProfit = false;
$now = date("Y-m-d");
if ($pitch['WindowEndDate'] && $now > $pitch['WindowEndDate']) {
  $status = "closed";
  $disableEdit = true;
} elseif ($pitch['CurrentAmount'] >= $pitch['TargetAmount'] && $pitch['TargetAmount'] > 0) {
  $status = "funded";
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
  <link rel="stylesheet" href="pitch_details.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
  <link rel="stylesheet" href="../footer.css">
  <link rel="stylesheet" href="../navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php include '../navbar.php'; ?>

  <div id="saveMessage" style="
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
">
    Pitch saved successfully!
  </div>

  <!-- Pitch Details Section -->
  <main class="section">
    <div class="pitch-card">
      <h2><?php echo htmlspecialchars($pitch['Title']); ?></h2>
      <p class="status <?php echo $status; ?>">Status: <?php echo ucfirst($status); ?></p>

      <h3>Elevator Pitch</h3>
      <p id="elevatorPitchText"><?php echo nl2br(htmlspecialchars($pitch['ElevatorPitch'])); ?></p>

      <h3>Detailed Pitch</h3>
      <p id="detailedPitchText"><?php echo nl2br(htmlspecialchars($pitch['DetailedPitch'])); ?></p>

      <h3>Tags</h3>
      <?php if ($tags): ?>
        <div class="tags-container">
          <?php foreach ($tags as $tagName): ?>
            <span class="tag"><?php echo htmlspecialchars($tagName); ?></span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No tags added for this pitch.</p>
      <?php endif; ?>

      <h3>Media</h3>
      <?php if ($mediaFiles): ?>
        <div class="media-container">
          <?php foreach ($mediaFiles as $fileUrl): ?>
            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $fileUrl)): ?>
              <img src="<?php echo htmlspecialchars($fileUrl); ?>" alt="Pitch Media" class="pitch-image">
            <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $fileUrl)): ?>
              <video controls class="pitch-video">
                <source src="<?php echo htmlspecialchars($fileUrl); ?>">
                Your browser does not support the video tag.
              </video>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No media uploaded for this pitch.</p>
      <?php endif; ?>

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

      <h3>Payout Frequency</h3>
      <p><strong><?php echo htmlspecialchars($pitch['PayoutFrequency']); ?></strong></p>

      <h3>Investment Tiers</h3>
      <?php if ($tiers): ?>
        <table class="tiers-table">
          <thead>
            <tr>
              <th>Tier Name</th>
              <th>Min (£)</th>
              <th>Max (£)</th>
              <th><span>Multiplier</span></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tiers as $tier): ?>
              <tr>
                <td><?php echo htmlspecialchars($tier['Name']); ?></td>
                <td><?php echo number_format($tier['Min'], 2); ?></td>
                <td><?php echo $tier['Max'] > 0 ? number_format($tier['Max'], 2) : '-'; ?></td>
                <td><?php echo htmlspecialchars($tier['Multiplier']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No investment tiers defined for this pitch yet.</p>
      <?php endif; ?>

      <div class="card-buttons">
        <a href="edit_pitch.php?id=<?php echo $pitch['PitchID']; ?>" class="edit-btn" <?php echo $disableEdit ? "style='pointer-events: none; opacity: 0.5;'" : ""; ?>>
          Edit Pitch
        </a>
        <?php if ($status === 'draft'): ?>
          <form action="submit_pitch.php" method="post" style="display:inline;">
            <input type="hidden" name="pitchId" value="<?php echo $pitch['PitchID']; ?>">
            <button type="submit" class="submit-btn">Submit Pitch</button>
          </form>
        <?php endif; ?>
        <?php if ($status === 'funded'): ?>
          <form action="profit_declare.php" method="get" style="display:inline;">
            <input type="hidden" name="id" value="<?php echo $pitch['PitchID']; ?>">
            <button type="submit" class="profit-btn">Declare Profit</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </main>
  <?php include '../footer.php'; ?>
  <!--<script src="pitch_details.js"></script> -->
</body>

</html>