<?php 
session_start();

// check if the user is logged in and as an investor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investor' || !isset($_SESSION['user_id'])) {
    // Redirect to log in
    header('Location: /login/login-investor.php'); 
    exit();
}

require_once dirname(__DIR__) . '/db.php';

// if db.php failed to connect to prevent crash
if (!isset($mysql) || !($mysql instanceof PDO)) {
    error_log("FATAL ERROR: \$mysql object not available in investor_pitch_details.php.");
    header('Location: /login/login-investor.php?error=db_unavail');
    exit();
}

// get pitch id, make an integer
$pitchID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pitchID <= 0) {
    header('Location: investor-portal-home.php?error=invalid_pitch');
    exit();
}

$investorID = $_SESSION['user_id'];
$existingInvestment = null;
$pitch = null;
$investmentTiers = [];

try {
    // check if there is an existing an investment from current user
    $sql_investment = "SELECT InvestmentID, Amount FROM Investment WHERE InvestorID = :investorID AND PitchID = :pitchID";
    $stmt_investment = $mysql->prepare($sql_investment);
    $stmt_investment->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_investment->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_investment->execute();
    $existingInvestment = $stmt_investment->fetch(PDO::FETCH_ASSOC);

    // get pitch details
    $sql_pitch = "SELECT BusinessID, CurrentAmount, TargetAmount, WindowEndDate, Title, ElevatorPitch, DetailedPitch, ProfitSharePercentage, PayoutFrequency FROM Pitch WHERE PitchID = :pitchID";
    $stmt_pitch = $mysql->prepare($sql_pitch);
    $stmt_pitch->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_pitch->execute();
    $pitch = $stmt_pitch->fetch(PDO::FETCH_ASSOC);

    if (!$pitch) {
        header('Location: investor-portal-home.php?error=pitch_not_found');
        exit();
    }
    
    // check if pitch is closed/funded for stopping investments
    $isFunded = (float)$pitch['CurrentAmount'] >= (float)$pitch['TargetAmount'];
    $isClosed = strtotime($pitch['WindowEndDate']) < time();
    $isInvestable = !$isFunded && !$isClosed;

    // get tiers for this pitch
    $sql_tiers = "SELECT Name, Min, Max, SharePercentage, Multiplier FROM InvestmentTier WHERE PitchID = :pitchID ORDER BY Min ASC";
    $stmt_tiers = $mysql->prepare($sql_tiers);
    $stmt_tiers->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_tiers->execute();
    $investmentTiers = $stmt_tiers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("DB Error fetching pitch details: " . $e->getMessage());
    header('Location: investor-portal-home.php?error=db_error');
    exit();
}

// to pass to js
$js_pitch_id = $pitchID;
$js_investment_id = $existingInvestment ? $existingInvestment['InvestmentID'] : 0;
$js_existing_amount = $existingInvestment ? $existingInvestment['Amount'] : ''; 
$js_is_investable = $isInvestable ? 'true' : 'false';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pitch Details - <?php echo htmlspecialchars($pitch['Title'] ?? 'Loading...'); ?></title>
    <link rel="stylesheet" href="investor_pitch_details.css" />
    <link rel="stylesheet" href="/navbar.css" />
    <link rel="stylesheet" href="/footer.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
</head>

<body>
    <div id="investor-navbar-placeholder"></div>

    <main class="section">
        <div class="pitch-card">
            <h2><?php echo htmlspecialchars($pitch['Title'] ?? 'Pitch Not Found'); ?></h2>
            <p class="status <?php echo $isInvestable ? 'active' : ($isFunded ? 'funded' : 'closed'); ?>">
                Status: <?php echo $isInvestable ? 'Active' : ($isFunded ? 'Funded' : 'Closed'); ?>
            </p>

            <!-- photos - PLACEHOLDERS -->
            <div class="media-gallery">
                <img src="https://placehold.co/400x200/50b857/white?text=Product+Image+1" alt="Pitch Image 1">
                <img src="https://placehold.co/400x200/50b857/white?text=Product+Image+2" alt="Pitch Image 2">
            </div>

            <h3>Elevator Pitch</h3>
            <p><?php echo htmlspecialchars($pitch['ElevatorPitch'] ?? 'Description not available.'); ?></p>

            <!-- details -->
                <h3>Detailed Pitch</h3>
            <div class="detailed-pitch-content">
                <p>
                    <?php echo nl2br(htmlspecialchars($pitch['DetailedPitch'] ?? 'Detailed pitch content not available.')); ?>
                </p>
            </div>

            <!-- funding progress -->
            <h3>Funding Progress</h3>
            <?php
                $current = (float)($pitch['CurrentAmount'] ?? 0);
                $target = (float)($pitch['TargetAmount'] ?? 1);
                $progress = ($target > 0) ? round(($current / $target) * 100) : 0;
            ?>
            <div class="progress-container">
                <div class="progress-bar" style="width: <?php echo $progress; ?>%;">£<?php echo number_format($current); ?> / £<?php echo number_format($target); ?></div>
            </div>
            <p class="meta-line"><strong>Funding Window Ends:</strong> <?php echo date('d M Y', strtotime($pitch['WindowEndDate'] ?? '')); ?></p>

            <!-- profit share -->
                <h3>Investor Profit Share</h3>
            <p>
                <strong><?php echo htmlspecialchars((float)$pitch['ProfitSharePercentage'] ?? 'N/A') . '%'; ?></strong> of profits distributed to investors.
                (Payout Frequency: 
                <strong><?php echo htmlspecialchars($pitch['PayoutFrequency'] ?? 'N/A'); ?></strong>)
            </p>


            <!-- tiers -->
            <h3>Investment Tiers</h3>
            <table class="tiers-table" id="tiers-table">
                <thead>
                    <tr>
                        <th>Tier</th>
                        <th>Min (£)</th>
                        <th>Max (£)</th>
                        <th>Profit Share (%)</th>
                        <th>Multiplier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($investmentTiers) > 0): ?>
                        <?php foreach ($investmentTiers as $tier): 
                            $min = number_format((float)$tier['Min']);
                            $max = (float)$tier['Max'];
                            $mult = number_format((float)$tier['Multiplier'], 1);
                            $share = number_format((float)$tier['SharePercentage'], 1);

                            // display values for the table
                            $maxDisplay = $max >= 9999999 ? $min . '+' : number_format($max);
                            $maxAttr = $max >= 9999999 ? $max : $max; 
                            
                            $maxTableCell = $max >= 9999999 ? '—' : number_format($max);
                        ?>
                            <tr data-tier="<?php echo htmlspecialchars($tier['Name']); ?>" 
                                data-min="<?php echo htmlspecialchars($tier['Min']); ?>" 
                                data-max="<?php echo htmlspecialchars($maxAttr); ?>" 
                                data-mult="<?php echo htmlspecialchars($mult); ?>">
                                <td><?php echo htmlspecialchars($tier['Name']); ?></td>
                                <td><?php echo $min; ?></td>
                                <td><?php echo $maxTableCell; ?></td>
                                <td><?php echo $share; ?>%</td>
                                <td><?php echo $mult; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                          <tr>
                            <td colspan="5">No investment tiers defined for this pitch.</td>
                          </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- invest form -->
            <div class="invest-card">
                <h3><?php echo $existingInvestment ? 'Update Your Investment' : 'Invest Now'; ?></h3>
                <form id="invest-form" class="invest-form">
                    <!-- fields to pass context to js -->
                    <input type="hidden" id="pitch-id" value="<?php echo $js_pitch_id; ?>">
                    <input type="hidden" id="investment-id" value="<?php echo $js_investment_id; ?>">

                    <label for="invest-amount">Amount (£)</label>
                    <input type="number" 
                        id="invest-amount" 
                        min="1" 
                        placeholder="e.g., 1200" 
                        value="<?php echo $js_existing_amount; ?>"
                        <?php echo $isInvestable ? '' : 'disabled'; ?>
                        required />

                    <div class="inline">
                        <div class="pill"><span class="label">Detected Tier:</span> <span id="detected-tier">—</span>
                        </div>
                        <div class="pill"><span class="label">Multiplier:</span> <span id="detected-mult">—</span></div>
                        <div class="pill"><span class="label">Calculated Shares:</span> <span id="calc-shares">—</span>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" 
                            class="btn primary" 
                            id="confirm-btn"
                            <?php echo $isInvestable ? '' : 'disabled'; ?>>
                            <?php echo $existingInvestment ? 'Update Investment' : 'Confirm Investment'; ?>
                        </button>
                        
                        <?php if ($existingInvestment): ?>
                        <button type="button" 
                            id="cancel-investment" 
                            class="btn danger outline"
                            <?php echo $isInvestable ? '' : 'disabled'; ?>
                            data-investment-id="<?php echo $js_investment_id; ?>">
                            Cancel Investment
                        </button>
                        <?php endif; ?>
                    </div>
                        <?php if (!$isInvestable): ?>
                            <p class="hint error-message">The investment window is closed or the pitch is fully funded.</p>
                        <?php endif; ?>

                    <p class="hint">You can update or cancel your investment while the funding window is open</p>
                </form>
            </div>
        </div>
    </main>

    <div src="/load-footer.js"></div>

    <script src="load_investor_navbar.js"></script>
    <script src="investor_pitch_details.js"></script>
    <script src="/load-footer.js"></script>
</body>

</html>
