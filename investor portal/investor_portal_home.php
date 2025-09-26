<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Portal Home</title>
    <link rel="stylesheet" href="investor_portal_home.css">
    <script src="https://kit.fontawesome.com/004961d7c9.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <header class="site-header">
        <a class="logo" href="#">Fundify</a>
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a class="nav-links" href="#">Discover</a></li>
                <li><a class="nav-links" href="#">My Portfolio</a></li>
                <li><a id="login" class="nav-links login" href="#">Logout</a></li>
            </ul>
        </nav>
    </header>

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
            // include your database connection file
            include 'db.php';

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
            ?>
        </div>
    </section>

</body>
</html>