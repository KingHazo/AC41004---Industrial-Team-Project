<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundify</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <main class="hero">
        <div class="hero-bg bg1"></div>
        <div class="hero-bg bg2"></div>

        <div class="hero-overlay">
            <h1>Empowering Small Businesses, Connecting Investors</h1>
            <p>A crowdfunding platform where businesses share ideas and investors support them.</p>

            <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
                <div class="hero-button">
                    <button onclick="window.location.href='login/login_signup.php?type=business'">Get Started as
                        Business</button>
                    <button onclick="window.location.href='login/login_signup.php?type=investor'">Get Started as
                        Investor</button>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>

<script src="Script.js"></script>

</html>
