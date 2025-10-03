<?php 
// start the session to get current investor
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

$investorID = $_SESSION['userId'];
$totalInvested = 0;
$recentInvestments = [];
$pitchFunding = []; // store the total amount raised per pitch
$dbError = null;

try {
    // find the total invested
    $sql_total = "SELECT SUM(Amount) AS TotalInvested FROM Investment WHERE InvestorID = :investorID";
    $stmt_total = $mysql->prepare($sql_total);
    $stmt_total->bindParam(':investorID', $investorID);
    $stmt_total->execute();
    $result_total = $stmt_total->fetch(PDO::FETCH_ASSOC);
    
    if ($result_total && $result_total['TotalInvested'] !== null) {
        // round to 2 decimal places
        $totalInvested = number_format($result_total['TotalInvested'], 2);
    } else {
        $totalInvested = number_format(0, 2);
    }

    // calc total ROI
    $sql_returns = "SELECT SUM(ROI) AS TotalReturns FROM Investment WHERE InvestorID = :investorID";
    $stmt_returns = $mysql->prepare($sql_returns);
    $stmt_returns->bindParam(':investorID', $investorID);
    $stmt_returns->execute();
    $result_returns = $stmt_returns->fetch(PDO::FETCH_ASSOC);
    $rawTotalReturns = $result_returns['TotalReturns'] ?? 0.00;
    $totalReturns = number_format($rawTotalReturns, 2);

    // how many active pitches
    $sql_active_count = "
        SELECT COUNT(DISTINCT T1.PitchID) AS ActiveCount 
        FROM Investment T1 
        INNER JOIN Pitch T2 ON T1.PitchID = T2.PitchID 
        WHERE T1.InvestorID = :investorID 
        AND T2.CurrentAmount < T2.TargetAmount 
        AND T2.WindowEndDate >= CURDATE()
    ";
    $stmt_active_count = $mysql->prepare($sql_active_count);
    $stmt_active_count->bindParam(':investorID', $investorID);
    $stmt_active_count->execute();
    $activePitchesCount = $stmt_active_count->fetchColumn();

    // find 3 most recent investments
    $sql_investments = "
        SELECT 
            I.InvestmentID, 
            I.Amount AS InvestmentAmount,            
            P.PitchID,
            P.Title AS PitchName,                    
            P.TargetAmount AS FundingGoal,           
            P.ProfitSharePercentage
        FROM Investment I
        JOIN Pitch P ON I.PitchID = P.PitchID
        WHERE I.InvestorID = :investorID
        ORDER BY I.InvestmentID DESC
        LIMIT 3
    ";
    $stmt_investments = $mysql->prepare($sql_investments);
    $stmt_investments->bindParam(':investorID', $investorID);
    $stmt_investments->execute();
    $recentInvestments = $stmt_investments->fetchAll(PDO::FETCH_ASSOC);

    // for each investment, fetch the current total funding for that pitch
    $pitchIds = array_column($recentInvestments, 'PitchID');

    if (!empty($pitchIds)) {
        $inPlaceholders = str_repeat('?,', count($pitchIds) - 1) . '?';
        
        // add up all investments for the pitches involved
        $sql_pitch_funding = "
            SELECT 
                PitchID, 
                SUM(Amount) AS CurrentFunding
            FROM Investment
            WHERE PitchID IN ($inPlaceholders)
            GROUP BY PitchID
        ";
        $stmt_pitch_funding = $mysql->prepare($sql_pitch_funding);
        $stmt_pitch_funding->execute($pitchIds);

        while ($row = $stmt_pitch_funding->fetch(PDO::FETCH_ASSOC)) {
            $pitchFunding[$row['PitchID']] = $row['CurrentFunding'];
        }
    }

} catch (PDOException $e) {
    $dbError = "Database Query Failed: " . $e->getMessage();
    error_log("Database Error in investor_portal_home.php: " . $dbError);
    $totalInvested = "DB Error"; 
    $recentInvestments = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Investor Dashboard</title>
    <link rel="stylesheet" href="investor_dashboard.css" />
    <link rel="stylesheet" href="../navbar.css" />
    <link rel="stylesheet" href="../footer.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body>
     <?php include '../navbar.php'; ?>

    <main class="section">
        
        <h2>My Portfolio</h2>

        <!-- kpi cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-label">Total Invested</p>
                <p class="kpi-value">£<?php echo htmlspecialchars($totalInvested); ?></p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Returns Received</p>
                <p class="kpi-value">£<?php echo htmlspecialchars($totalReturns); ?></p>
            </div>
            <div class="kpi-card">
                <p class="kpi-label">Active Investments</p>
                <p class="kpi-value"><?php echo htmlspecialchars($activePitchesCount); ?></p>
            </div>
        </div>

        <!-- quick actions -->
        <div class="actions">
            <a class="btn primary" href="investor_portal_home.php">Browse Pitches</a>
            <a class="btn" href="my_investments.php">View My Investments</a>
        </div>

        <!-- recent holdings -->
        <h3>Recent Holdings (Last <?php echo count($recentInvestments); ?> Investments)</h3>
        <div class="pitches">
            
            <?php if (empty($recentInvestments) && !$dbError): ?>
                <p>You have no recent investments.</p>
                <?php if ($totalInvested === number_format(0, 2)): ?>
                    <!-- <p style="color: blue;">(Total Invested is £0.00)</p> -->
                <?php endif; ?>
            <?php elseif (!empty($recentInvestments)): ?>
                <?php foreach ($recentInvestments as $investment): 
                    $pitchID = $investment['PitchID'];
                    $currentFunding = $pitchFunding[$pitchID] ?? 0;
                    $fundingGoal = $investment['FundingGoal']; // TargetAmount
                    $investedAmount = $investment['InvestmentAmount']; // Amount
                    $profitShare = $investment['ProfitSharePercentage'];
                    
                    // progress percentage
                    $progressPct = ($fundingGoal > 0) ? round(($currentFunding / $fundingGoal) * 100) : 0;
                    $progressDisplay = "£" . number_format($currentFunding, 0) . " / £" . number_format($fundingGoal, 0);

                    $pitchName = htmlspecialchars($investment['PitchName']); // Title
                    $investedDisplay = "£" . number_format($investedAmount, 2);
                ?>
                <div class="card">
                    <h4><?php echo $pitchName; ?></h4>
                    <div class="progress-container">
                        <!-- progress Bar -->
                        <div class="progress-bar" style="width: <?php echo $progressPct; ?>%;"><?php echo $progressDisplay; ?></div>
                    </div>
                    <div class="meta">
                        <!-- profit share -->
                        <span class="profit-share">Profit Share: <strong><?php echo htmlspecialchars($profitShare); ?>%</strong></span>
                        <!-- amount invested -->
                        <span class="invested">You invested: <strong><?php echo $investedDisplay; ?></strong></span>
                    </div>
                    <div class="card-buttons">
                        <!-- PitchID is data id -->
                        <button class="view-btn" data-id="<?php echo $pitchID; ?>">View</button>
                        <button class="cancel-btn" data-id="<?php echo $investment['InvestmentID']; ?>">Cancel Investment</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </main>


    <div id="footer-placeholder"></div>

    
    <script src="investor_dashboard.js"></script>
    <script src="../load_footer.js"></script>
</body>

</html>
