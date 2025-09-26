<?php
session_start();

// 1. Authentication Check: Redirects non-investors back to the login page.
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investor') {
    // Note: The redirection path needs to be correct relative to the document root (/)
    header('Location: /login/login-investor.php'); 
    exit();
}

// 2. Database connection: Include this only once at the top
include '../db.php';
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
                $sql = "SELECT * FROM Pitch";
                $stmt = $mysql->query($sql);
 
                // make a card for each pitch
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $progress_percentage = ($row['CurrentAmount'] / $row['TargetAmount']) * 100;
                    ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($row['ElevatorPitch']); ?></p>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;">
                                £<?php echo number_format($row['CurrentAmount']); ?> / £<?php echo number_format($row['TargetAmount']); ?>
                            </div>
                        </div>
                        <div class="profit-share">
                            Investor Profit Share: <strong><?php echo htmlspecialchars($row['ProfitSharePercentage']); ?>%</strong>
                        </div>
                    <div class="card-buttons">
                        <button class="invest-btn">Invest</button>
                        <button class="more-btn">Find Out More</button>
                    </div>
                </div>
            <?php
            }
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
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