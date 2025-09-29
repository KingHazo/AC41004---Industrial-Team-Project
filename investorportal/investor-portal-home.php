<?php session_start();
// CRITICAL FIX: session_start() MUST be the very first thing executed, placed immediately after the opening tag.

// 1. Authentication Check: Redirects non-investors back to the login page.
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investor') {
    // If the session is invalid, redirect immediately.
    // The path starting with '/' is the most reliable way to redirect from any file location.
    header('Location: /login/login-investor.php'); 
    exit();
}

// 2. Database connection: Using the absolute path to guarantee the file is found.
// This path is reliable and bypasses issues caused by ambiguous relative paths (like '../').
require_once dirname(__DIR__) . '/db.php';

// CRITICAL SAFETY CHECK: Prevent 500 error if db.php failed to connect or load.
if (!isset($mysql) || !($mysql instanceof PDO)) {
    // If the database object is missing, log the error and redirect gracefully 
    // instead of crashing with a generic 500 Internal Server Error.
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
            // Database connection is established and checked at the top of the file
            try {
                // select all pitches
                $sql = "SELECT * FROM Pitch";
                $stmt = $mysql->query($sql);
 
                // make a card for each pitch
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Prevent division by zero error
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
                        <button class="more-btn">Find Out More</button>
                    </div>
                </div>
                <?php
                }
            } catch (PDOException $e) {
                // If the query fails (e.g., table name typo), show a friendly message
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
