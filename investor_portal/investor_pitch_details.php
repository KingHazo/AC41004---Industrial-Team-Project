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


if (!$mysql) {
  die("Database connection failed.");
}

// get pitch id, make an integer
$pitchID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pitchID <= 0) {
    header('Location: investor_portal_home.php?error=invalid_pitch');
    exit();
}

$investorID = $_SESSION['userId'];
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
        header('Location: investor_portal_home.php?error=pitch_not_found');
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
    header('Location: investor_portal_home.php?error=db_error');
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
      <link rel="stylesheet" href="investor_pitch_details.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../navbar.css" />
    <link rel="stylesheet" href="../footer.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
    rel="stylesheet" />

    <style>
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #ffffff;
            /* Spinner color to match button text */
            border-radius: 50%;
            width: 1.25rem;
            height: 1.25rem;
            animation: spin 1s linear infinite;
            display: none;
            /* Controlled by JS */
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .alert-message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            display: none;
            /* Initially hidden */
        }

        .alert-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .actions {
            display: flex;
            /* Ensure buttons are side-by-side */
            gap: 10px;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>

    <main class="section">
        <div class="pitch-card">
            <h2><?php echo htmlspecialchars($pitch['Title'] ?? 'Pitch Not Found'); ?></h2>
            <p class="status <?php echo $isInvestable ? 'active' : ($isFunded ? 'funded' : 'closed'); ?>">
                Status: <?php echo $isInvestable ? 'Active' : ($isFunded ? 'Funded' : 'Closed'); ?>
            </p>

            <div id="messageBox" class="alert-message hidden" role="alert">
                <p id="messageContent"></p>
            </div>

            <!-- Slideshow container for images-->
             <!-- https://www.w3schools.com/howto/howto_js_slideshow.asp-->
            <div class="slideshow-container">

                <!-- Full-width images with number and caption text -->
                <div class="mySlides fade">
                    <img src="image1.jpg" style="width:100%">
                </div>

                <div class="mySlides fade">
                    <img src="image2.jpg" style="width:100%">
                </div>

                <div class="mySlides fade">
                    <img src="image3.jpg" style="width:100%">
                </div>

                <!-- Next and previous buttons -->
                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>

            <!-- The dots/circles -->
            <div style="text-align:center">
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
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
                        <th><span>Multiplier</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($investmentTiers) > 0): ?>
                        <?php foreach ($investmentTiers as $tier):
                            $min = number_format((float)$tier['Min']);
                            $max = (float)$tier['Max'];
                            $mult = number_format((float)$tier['Multiplier'], 1);

                            $isUnlimited = $max >= 9999999;
                            
                            $maxTableCell = $isUnlimited ? 'No Limit' : number_format($max);
                            
                            $maxAttr = $max;
                        ?>
                            <tr data-tier="<?php echo htmlspecialchars($tier['Name']); ?>"
                                data-min="<?php echo htmlspecialchars($tier['Min']); ?>"
                                data-max="<?php echo htmlspecialchars($maxAttr); ?>"
                                data-mult="<?php echo htmlspecialchars($mult); ?>">
                                <td><?php echo htmlspecialchars($tier['Name']); ?></td>
                                <td><?php echo $min; ?></td>
                                <td><?php echo $maxTableCell; ?></td>
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
            <?php
            $shouldShowInvestCard = $isInvestable;
            if ($shouldShowInvestCard):
            ?>
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
                                data-shares="0"
                                <?php echo $isInvestable ? '' : 'disabled'; ?>>
                                <span id="buttonText"><?php echo $existingInvestment ? 'Update Investment' : 'Confirm Investment'; ?></span>
                                <div id="loadingSpinner" class="spinner"></div>
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
            <?php else:
                $blockReason = '';
                if ($isFunded && $isClosed) {
                    $blockReason = 'This pitch is fully funded and the investment window has closed.';
                } elseif ($isFunded) {
                    $blockReason = 'This pitch has reached its target funding amount and is now fully funded.';
                } elseif ($isClosed) {
                    $blockReason = 'The investment window for this pitch closed on ' . date('d M Y', strtotime($pitch['WindowEndDate'])) . '. Further investments or updates are not possible.';
                }
            ?>
                <div class="invest-card invest-status-message">
                    <h3>Investment Unavailable</h3>
                    <p><?php echo nl2br(htmlspecialchars($blockReason)); ?></p>
                    <?php if ($existingInvestment): ?>
                        <p class="hint" style="margin-top: 1rem; color: #555;">
                            You hold an existing investment of £<?php echo number_format($existingInvestment['Amount']); ?>. This investment is now locked and cannot be updated or canceled.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="investor_pitch_details.js?v=<?php echo time(); ?>"></script>
</body>

</html>