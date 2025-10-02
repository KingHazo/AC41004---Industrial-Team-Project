<?php session_start();

// check if the user is logged in and as an investor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investor') {
    // redirect to log in
    header('Location: /login/login-investor.php'); 
    exit();
}

require_once dirname(__DIR__) . '/db.php';

// if db.php failed to connect to prevent crash
if (!isset($mysql) || !($mysql instanceof PDO)) {
    error_log("FATAL ERROR: \$mysql object not available in investor-portal-home.php.");
    header('Location: /login/login-investor.php?error=db_unavail');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Portal Home</title>
    <link rel="stylesheet" href="investor-portal-home.css">
    <link rel="stylesheet" href="../footer.css">
    <script src="https://kit.fontawesome.com/004961d7c9.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div id="investor-navbar-placeholder"></div>

    <!-- discover new pitches section-->
    <section id="discover" class="section">
        <h2>Discover New Pitches</h2>
        <div class="search-filter">
            <input type="text" placeholder="Search pitches...">
            <button class="filter-btn" aria-label="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
        <div class="active-filters">
            <span class="filter-tag">Eco Friendly</span>
            <span class="filter-tag">High Risk</span>
            <span class="filter-tag">Low Risk</span>
        </div>

        <div class="pitches">
            <?php
            try {
                // select all pitches
                $sql = "SELECT PitchID, Title, ElevatorPitch, CurrentAmount, TargetAmount, ProfitSharePercentage FROM Pitch";
                $stmt = $mysql->query($sql);
 
                // make a card for each pitch
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $pitch_id = $row['PitchID'];

                    if (empty($pitch_id)) {
                        error_log("Skipping pitch card due to missing PitchID in database record.");
                        continue;
                    }

                    // stop division by zero error
                    $currentAmount = $row['CurrentAmount'] ?? 0;
                    $targetAmount = $row['TargetAmount'] ?? 1;
                    
                    $progress_percentage = ($currentAmount / $targetAmount) * 100;
                    $progress_percentage = min($progress_percentage, 100); // Cap at 100%
                    
                    ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($row['Title'] ?? 'N/A'); ?></h3>
                        <p><?php echo htmlspecialchars($row['ElevatorPitch'] ?? 'N/A'); ?></p>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;">
                                £<?php echo number_format($currentAmount); ?> / £<?php echo number_format($targetAmount); ?>
                            </div>
                        </div>
                        <div class="profit-share">
                            Investor Profit Share: <strong><?php echo htmlspecialchars($row['ProfitSharePercentage'] ?? '0'); ?>%</strong>
                        </div>
                    <div class="card-buttons">
                        <button class="invest-btn">Invest</button>
                        <button class="more-btn" data-pitch-id="<?php echo htmlspecialchars($pitch_id); ?>">Find Out More</button>
                    </div>
                </div>
                <?php
                }
            } catch (PDOException $e) {
                // if the query fails
                echo "<p style='color: red; text-align: center; margin-top: 20px;'>Error loading pitches: " . htmlspecialchars($e->getMessage()) . "</p>";
                error_log("Pitch Load Query Error: " . $e->getMessage());
            }
            ?>
        </div>
    </section>
    
    <div id="footer-placeholder"></div>
    <script src="load_investor_navbar.js"></script>
    <script src="../load-footer.js"></script>
    <script src="investor-portal-home.js"></script>
</body>

</html>
