<?php
// start the session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// this makes sure all links point to the correct location regardless of the current folder
$base_url = '/AC41004---Industrial-Team-Project';
?>

<header class="site-header">
    <a class="logo" href="<?= $base_url ?>/index.php">Fundify</a>
    <nav class="main-nav">
        <ul class="nav-list">

            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>

                <!-- Logged-in menu -->
                <?php if ($_SESSION['userType'] === 'investor'): ?>
                    <li><a class="nav-links" href="<?= $base_url ?>/investor_portal/investor_portal_home.php">Discover</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/investor_portal/investor_portfolio.php">My Portfolio</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/login/logout.php">Logout</a></li>
                <?php elseif ($_SESSION['userType'] === 'business'): ?>
                    <li><a class="nav-links" href="<?= $base_url ?>/business_portal/business_dashboard.php">Dashboard</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/business_portal/create_pitch.php">Create Pitch</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/login/logout.php">Logout</a></li>
                <?php endif; ?>

            <?php else: ?>

                <!-- Not logged-in menu -->
                <li><a class="nav-links" href="<?= $base_url ?>/index.php">Home</a></li>
                <li><a class="nav-links" href="<?= $base_url ?>/about.php">About</a></li>
                <li><a class="nav-links" href="<?= $base_url ?>/login/login_signup.php">Login/Sign-up</a></li>

            <?php endif; ?>

        </ul>
    </nav>
</header>
