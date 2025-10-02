<?php 
// start the session to get current business
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// make sure user is logged in and is a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'investor') {
    header("Location: ../login/login_signup.php");
    exit();
}

// include database connection
include '../sql/db.php';


$investorID = $_SESSION['user_id'];
$investmentsData = [];
$tiersByPitch = [];
$dbError = null;

try {
    // 1. get investments and pitch details
    $sql_investments = "
        SELECT 
            I.InvestmentID, 
            I.Amount, 
            I.ROI, 
            I.DateMade,
            P.PitchID,
            P.Title,
            P.TargetAmount,
            P.CurrentAmount,
            P.WindowEndDate
        FROM Investment I
        JOIN Pitch P ON I.PitchID = P.PitchID
        WHERE I.InvestorID = :investorID
        ORDER BY I.DateMade DESC
    ";
    $stmt_investments = $mysql->prepare($sql_investments);
    $stmt_investments->bindParam(':investorID', $investorID);
    $stmt_investments->execute();
    $investmentsData = $stmt_investments->fetchAll(PDO::FETCH_ASSOC);

    // get all investment tiers
    $sql_tiers = "SELECT PitchID, Name, Multiplier, Min, Max FROM InvestmentTier";
    $stmt_tiers = $mysql->prepare($sql_tiers);
    $stmt_tiers->execute();
    $allTiers = $stmt_tiers->fetchAll(PDO::FETCH_ASSOC);

    // organise by pitch id to aid lookup
    foreach ($allTiers as $tier) {
        $pitchId = $tier['PitchID'];
        if (!isset($tiersByPitch[$pitchId])) {
            $tiersByPitch[$pitchId] = [];
        }
        $tiersByPitch[$pitchId][] = $tier;
    }

} catch (PDOException $e) {
    $dbError = "Database Query Failed: " . $e->getMessage();
    error_log("Database Error in my_investments.php: " . $dbError);
}

// finds the tier details based on the amount and pitch
function getInvestmentTier($pitchID, $amount, $tiersByPitch) {
    if (!isset($tiersByPitch[$pitchID])) {
        return ['Name' => 'N/A', 'Multiplier' => 1.0];
    }

    foreach ($tiersByPitch[$pitchID] as $tier) {
        $min = (float)$tier['Min'];
        $max = (float)$tier['Max'];
        $amount = (float)$amount;

        if ($amount >= $min && $amount <= $max) {
            return $tier;
        }
    }
    // if no tier matches
    return ['Name' => 'Default', 'Multiplier' => 1.0];
}

// current status of pitch
function getPitchStatus($currentAmount, $targetAmount, $windowEndDate) {
    if ($currentAmount >= $targetAmount) {
        return 'funded';
    }
    // if the end date has passed
    if (strtotime($windowEndDate) < time()) {
        return 'closed';
    }
    return 'active';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Investments</title>
    <link rel="stylesheet" href="my_investments.css" />

     <link rel="stylesheet" href="../footer.css" />
    <link rel="stylesheet" href="../navbar.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body>
    <!-- navbar -->
   
     <?php include '../navbar.php'; ?>

    <main class="section">
        <h2>My Investments</h2>

        <!-- filters -->
        <div class="filters">
            <button class="chip active" data-filter="all">All</button>
            <button class="chip" data-filter="active">Active</button>
            <button class="chip" data-filter="funded">Funded</button>
            <button class="chip" data-filter="closed">Closed</button>
        </div>

        <!-- investments list -->
        <div id="investments" class="investments">
            <?php if (empty($investmentsData) && !$dbError): ?>
                <p style="padding: 20px; text-align: center; color: #555;">You have not made any investments yet.</p>
            <?php elseif (!empty($investmentsData)): ?>
                <?php foreach ($investmentsData as $inv): 
                    $pitchStatus = getPitchStatus($inv['CurrentAmount'], $inv['TargetAmount'], $inv['WindowEndDate']);
                    $tier = getInvestmentTier($inv['PitchID'], $inv['Amount'], $tiersByPitch);
                    
                    $tierName = htmlspecialchars($tier['Name']);
                    $multiplier = htmlspecialchars(number_format($tier['Multiplier'], 1));
                    $investedAmount = htmlspecialchars(number_format($inv['Amount'], 2));
                    $shares = htmlspecialchars(number_format($inv['Amount'] * $tier['Multiplier'], 0)); // calculate shares
                    $profitToDate = htmlspecialchars(number_format($inv['ROI'], 2)); // ROI for profit
                    
                    $progressPct = ($inv['TargetAmount'] > 0) ? round(($inv['CurrentAmount'] / $inv['TargetAmount']) * 100) : 0;
                    $progressDisplay = "£" . number_format($inv['CurrentAmount'], 0) . " / £" . number_format($inv['TargetAmount'], 0);
                    
                    // classes and button state
                    $statusClass = $pitchStatus;
                    $profitClass = ($inv['ROI'] > 0) ? 'good' : 'muted';
                    $cancelDisabled = ($pitchStatus === 'funded' || $pitchStatus === 'closed') ? 'disabled' : '';
                    $cancelTitle = $cancelDisabled ? 'title="Cannot cancel after funding closed or window expires"' : '';
                ?>
                <!-- Card -->
                <article class="inv-card" data-status="<?php echo $pitchStatus; ?>">
                    <div class="inv-head">
                        <h3><?php echo htmlspecialchars($inv['Title']); ?></h3>
                        <span class="status badge <?php echo $statusClass; ?>"><?php echo ucfirst($pitchStatus); ?></span>
                    </div>

                    <div class="grid">
                        <div class="col">
                            <p class="muted">You invested</p>
                            <p class="num">£<?php echo $investedAmount; ?></p>
                        </div>
                        <div class="col">
                            <p class="muted">Tier</p>
                            <p><?php echo $tierName; ?> (×<?php echo $multiplier; ?>)</p>
                        </div>
                        <div class="col">
                            <p class="muted">Shares</p>
                            <p><?php echo $shares; ?></p>
                        </div>
                        <div class="col">
                            <p class="muted">Profit to date</p>
                            <p class="<?php echo $profitClass; ?>">£<?php echo $profitToDate; ?></p>
                        </div>
                    </div>

                    <div class="progress-container">
                        <div class="progress-bar" style="width:<?php echo $progressPct; ?>%;"><?php echo $progressDisplay; ?></div>
                    </div>

                    <div class="actions">
                        <button class="btn" data-action="view" data-id="<?php echo $inv['PitchID']; ?>">View Details</button>
                        <button class="btn danger outline" data-action="cancel" data-id="<?php echo $inv['InvestmentID']; ?>" <?php echo $cancelDisabled; ?> <?php echo $cancelTitle; ?>>Cancel Investment</button>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>


     <?php include '../footer.php'; ?>

    <script src="my_investments.js"></script>
  
</body>

</html>
