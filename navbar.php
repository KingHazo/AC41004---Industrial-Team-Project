<?php
// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// base URL for correct paths
$base_url = 'http://fundify.us-east-1.elasticbeanstalk.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fundify</title>
<style>
/* Header */
.site-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background: #ffffff;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    font-family: 'Montserrat', sans-serif;
    position: relative;
}

.logo {
    font-weight: 700;
    font-size: 1.25rem;
    color: #0b3d91;
    text-decoration: none;
}

/* Nav container */
.nav-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Nav list */
.nav-list {
    list-style: none;
    display: flex;
    gap: 15px;
    margin: 0;
    padding: 0;
}

.nav-links {
    text-decoration: none;
    color: #333;
    transition: color 0.2s;
}

.nav-links:hover {
    color: #0b3d91;
}

/* Hamburger */
.hamburger {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 25px;
    height: 20px;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 110; /* make sure it's above the menu */
}

.hamburger span {
    display: block;
    height: 3px;
    width: 100%;
    background: #333;
    border-radius: 2px;
}

/* Mobile menu */
@media (max-width: 768px) {
    .nav-list {
        flex-direction: column;
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: 220px;
        background: #fff;
        padding: 4rem 1rem;
        gap: 20px;
        transform: translateX(100%); /* hidden by default */
        transition: transform 0.3s ease-in-out;
        box-shadow: -2px 0 6px rgba(0,0,0,0.2);
        z-index: 100;
    }

    .nav-list.show {
        transform: translateX(0); /* slide in */
    }

    .hamburger {
        display: flex;
    }
}

/* Desktop menu */
@media (min-width: 769px) {
    .nav-list {
        display: flex !important;
        flex-direction: row;
        gap: 15px;
        position: static;
        width: auto;
        padding: 0;
        box-shadow: none;
        background: none;
        transform: none !important;
    }
    .hamburger {
        display: none;
    }
}
</style>
</head>
<body>

<header class="site-header">
    <a class="logo" href="<?= $base_url ?>/index.php">Fundify</a>

    <div class="nav-container">
        <ul class="nav-list">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <?php if ($_SESSION['userType'] === 'investor'): ?>
                    <li><a class="nav-links" href="<?= $base_url ?>/investor_portal/investor_portal_home.php">Discover</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/investor_portal/investor_dashboard.php">My Portfolio</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/investor_portal/investor_profile.php">My Account</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/login/logout.php">Logout</a></li>
                <?php elseif ($_SESSION['userType'] === 'business'): ?>
                    <li><a class="nav-links" href="<?= $base_url ?>/business_portal/business_dashboard.php">Dashboard</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/business_portal/create_pitch.php">Create Pitch</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/business_portal/business_profile.php">My Account</a></li>
                    <li><a class="nav-links" href="<?= $base_url ?>/login/logout.php">Logout</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a class="nav-links" href="<?= $base_url ?>/index.php">Home</a></li>
                <li><a class="nav-links" href="<?= $base_url ?>/about.php">About</a></li>
                <li><a class="nav-links" href="<?= $base_url ?>/login/login_signup.php">Login/Sign-up</a></li>
            <?php endif; ?>
        </ul>

        <button class="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navList = document.querySelector('.nav-list');

    hamburger.addEventListener('click', () => {
        navList.classList.toggle('show');
    });
});
</script>

</body>
</html>
