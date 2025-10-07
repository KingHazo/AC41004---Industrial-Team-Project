<?php
// start session and include DB
if (session_status() === PHP_SESSION_NONE) session_start();
include '../sql/db.php';

// make sure user is logged in and is a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    die("Access denied.");
}

// check for PitchID in GET or POST
$pitchId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['pitch_id']) ? (int)$_POST['pitch_id'] : 0);
if (!$pitchId) die("Pitch ID missing.");

// fetch pitch info
$stmt = $mysql->prepare("SELECT * FROM Pitch WHERE PitchID = :pitchId AND BusinessID = :businessId");
$stmt->bindParam(':pitchId', $pitchId);
$stmt->bindParam(':businessId', $_SESSION['userId']);
$stmt->execute();
$pitch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pitch) die("Pitch not found or you do not have permission.");

$investorSharePercent = $pitch['ProfitSharePercentage'] ?? 0;
$currentAmount = $pitch['CurrentAmount'] ?? 0;
$status = ucfirst($pitch['Status']);
$title = htmlspecialchars($pitch['Title']);
$successMessage = "";

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalProfit = floatval($_POST['profit']);
    $distributableProfit = ($totalProfit * $investorSharePercent) / 100;

    // fetch all investments for this pitch. 
    $stmt = $mysql->prepare("
        SELECT i.InvestmentID, i.InvestorID, i.Amount, i.ROI, t.Multiplier
        FROM Investment i
        INNER JOIN InvestmentTier t
            ON i.PitchID = t.PitchID
        WHERE i.PitchID = :pitchId
        AND (
            -- 1. Standard Case: Amount falls within the tier's defined Min and Max
            i.Amount BETWEEN t.Min AND t.Max
            OR
            -- 2. Overflow Case: The investment amount is greater than the MIN of the highest tier.
            -- This assigns amounts exceeding the highest tier's Max to that tier.
            (
                i.Amount >= t.Min 
                AND t.Min = (SELECT MAX(Min) FROM InvestmentTier WHERE PitchID = :pitchId)
            )
        )
    ");
    $stmt->bindParam(':pitchId', $pitchId);
    $stmt->bindParam(':pitchId', $pitchId); 
    $stmt->execute();
    $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // calculate total shares (based on Investment Amount * Tier Multiplier)
    $totalShares = 0;
    foreach ($investments as $inv) {
        $shares = $inv['Amount'] * $inv['Multiplier'];
        $totalShares += $shares;
    }

    // avoid division by zero
    if ($totalShares > 0) {
        $profitPerShare = $distributableProfit / $totalShares;

        // Distribute profit to each investor, updating ROI and Balance
        foreach ($investments as $inv) {
            $investmentId = $inv['InvestmentID'];
            $investorId = $inv['InvestorID'];
            $shares = $inv['Amount'] * $inv['Multiplier'];
            $profitForInvestor = $shares * $profitPerShare;
            
            $stmt = $mysql->prepare("UPDATE Investment SET ROI = ROI + :profit WHERE InvestmentID = :investmentId");
            $stmt->bindParam(':profit', $profitForInvestor);
            $stmt->bindParam(':investmentId', $investmentId);
            $stmt->execute();

            $stmt = $mysql->prepare("UPDATE Investor SET InvestorBalance = InvestorBalance + :profit WHERE InvestorID = :investorId");
            $stmt->bindParam(':profit', $profitForInvestor);
            $stmt->bindParam(':investorId', $investorId);
            $stmt->execute();
        }
    }

    $stmt = $mysql->prepare("INSERT INTO ProfitDistribution (PitchID, Profit, DistributionDate) VALUES (:pitchId, :profit, NOW())");
    $stmt->bindParam(':pitchId', $pitchId);
    $stmt->bindParam(':profit', $distributableProfit);
    $stmt->execute();

    $successMessage = "Profits of £" . number_format($distributableProfit, 2) . " distributed to investors!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profit Declaration</title>
  <link rel="stylesheet" href="profit_declare.css">
  <link rel="stylesheet" href="../footer.css">
  <link rel="stylesheet" href="../navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Popup message styling */
    #popupMessage {
      display: none;
      position: fixed;
      top: 10px;
      left: 50%;
      transform: translateX(-50%);
      background: #27ae60;
      color: #fff;
      padding: 12px 20px;
      border-radius: 6px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      transition: opacity 0.3s ease;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <?php include '../navbar.php'; ?>

    <!-- Popup container -->
  <div id="popupMessage"></div>

  <!-- Profit declaration section -->
    <main class="section">
    <div class="profit-card">
      <h2>Declare Profits – <?php echo $title; ?></h2>
      <p><strong>Status:</strong> <?php echo $status; ?></p>
      <p><strong>Raised:</strong> £<?php echo number_format($currentAmount, 2); ?> | 
          <strong>Investor Share:</strong> <?php echo $investorSharePercent; ?>%</p>

      <form class="profit-form" method="POST" action="">
        <input type="hidden" name="pitch_id" value="<?php echo $pitchId; ?>">

        <label for="profit">Total Company Profit (£)</label>
        <input type="number" id="profit" name="profit" placeholder="Enter profit amount" required>

        <label for="distributable">Total Distributable Profit (£)</label>
        <input type="text" id="distributable" readonly value="£0">

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Distribute Profits</button>
        </div>
      </form>
    </div>
  </main>
  <?php include '../footer.php'; ?>

  <script>
    // live calculation for distributable profit
    const profitInput = document.getElementById('profit');
    const distributableField = document.getElementById('distributable');
    const investorShare = <?php echo $investorSharePercent; ?>;

    if (profitInput && distributableField) {
        profitInput.addEventListener('input', () => {
            const profit = parseFloat(profitInput.value) || 0;
            distributableField.value = `£${(profit * investorShare / 100).toFixed(2)}`;
        });
    }

    // show popup if there's a success message
    const successMessage = "<?php echo $successMessage; ?>";
    if (successMessage) {
      const popup = document.getElementById('popupMessage');
      popup.textContent = successMessage;
      popup.style.display = 'block';
      popup.style.opacity = 1;

      // hide after 3 seconds with fade
      setTimeout(() => {
        popup.style.opacity = 0;
        setTimeout(() => { popup.style.display = 'none'; }, 300);
      }, 3000);
    }
  </script>
  <script src="profit_declare.js"></script>
</body>

</html>
