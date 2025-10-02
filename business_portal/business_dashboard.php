<?php
// start the session to get current business
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

// get the current business ID
$businessId = $_SESSION['userId'];

// fetch all pitches for this business
$stmt = $mysql->prepare("SELECT * FROM Pitch WHERE BusinessID = :businessId");
$stmt->bindParam(':businessId', $businessId);
$stmt->execute();
$pitches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Business Dashboard</title>
  <link rel="stylesheet" href="business_dashboard.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
  <link rel="stylesheet" href="../footer.css">
  <link rel="stylesheet" href="../navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php include '../navbar.php'; ?>

  <section id="dashboard" class="section">
    <h2>My Pitches</h2>

    <!-- add new pitch -->
    <div class="create-new">
      <a href="create_pitch.php" class="create-btn">+ Create New Pitch</a>
    </div>

    <div class="pitches">
      <?php foreach ($pitches as $pitch):
        // calculate progress percentage
        $progress = $pitch['TargetAmount'] > 0 ? ($pitch['CurrentAmount'] / $pitch['TargetAmount']) * 100 : 0;

        // determine status
        $status = "draft";
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
        ?>
        <div class="card">
          <h3><?php echo htmlspecialchars($pitch['Title']); ?></h3>
          <p>Status: <span class="status <?php echo $status; ?>"><?php echo ucfirst($status); ?></span></p>
          <div class="progress-container">
            <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
              £<?php echo number_format($pitch['CurrentAmount'], 2); ?> /
              £<?php echo number_format($pitch['TargetAmount'], 2); ?>
            </div>
          </div>
          <div class="card-buttons">
            <form action="pitch_details.php" method="get" style="display:inline;">
              <input type="hidden" name="id" value="<?php echo $pitch['PitchID']; ?>">
              <button type="submit" class="view-btn">View</button>
            </form>

            <form action="profit_declare.php" method="get" style="display:inline;">
              <input type="hidden" name="id" value="<?php echo $pitch['PitchID']; ?>">
              <button type="submit" class="profit-btn">Declare Profit</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php include '../footer.php'; ?>

  <script src="business_dashboard.js"></script>
</body>


</html>