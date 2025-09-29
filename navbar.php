<?php
// this makes sure all links point to the correct location regardless of the current folder
$base_url = '/AC41004---Industrial-Team-Project';

// only show this navbar if user is NOT logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true):
?>

<header class="site-header">
    <a class="logo" href="index.php">Fundify</a>
    <nav class="main-nav">
        <ul class="nav-list">
             <li><a class="nav-links" href="<?= $base_url ?>/index.php">Home</a></li>
            <li><a class="nav-links" href="<?= $base_url ?>/about.php">About</a></li>
            <li class="dropdown">
                <span class="nav-links">Sign Up <span class="arrow">▼</span></span>
                <ul class="dropdown-menu">
                    <li><a href="<?= $base_url ?>/login/signup_business.php">As Business Owner</a></li>
                    <li><a href="<?= $base_url ?>/login/signup_investor.php">As Investor</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <span class="nav-links">Login <span class="arrow">▼</span></span>
                <ul class="dropdown-menu">
                    <li><a href="<?= $base_url ?>/login/login_business.php">As Business Owner</a></li>
                    <li><a href="<?= $base_url ?>/login/login_investor.php">As Investor</a></li>
                </ul>
            </li>

        </ul>
    </nav>
</header>

<?php
endif; // end check for not logged in
?>
