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
    <link rel="stylesheet" href="navbar.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
</head>

<body class="home">
    <?php include 'navbar.php'; ?>

    <main class="hero">
        <div class="hero-bg bg1"></div>
        <div class="hero-bg bg2"></div>

        <div tabindex="0" class="hero-overlay">
            <h1>
                Empowering Small Businesses<br>
                Connecting Investors
            </h1>

            <p class="hero-sub">
                A crowdfunding platform where businesses share ideas and investors support them.
            </p>

            <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
                <div class="hero-button">
                    <button
                        onclick="window.location.href='login/login_signup.php?type=business'"
                        aria-label="Sign up as a business on Fundify"
                    >
                        Get Started as Business
                    </button>

                    <button
                        onclick="window.location.href='login/login_signup.php?type=investor'"
                        aria-label="Sign up as an investor on Fundify"
                    >
                        Get Started as Investor
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <section class="section info-cards">
        <article class="card" tabindex="0">
            <h2>Transparent Profit Sharing</h2>
            <p>
                Clear tiers, visible funding progress, and simple profit splits.
                Investors know exactly how returns are calculated.
            </p>
        </article>

        <article class="card" tabindex="0">
            <h2>AI-Assisted Pitches</h2>
            <p>
                Founders get instant suggestions to improve clarity, market fit,
                and financial storytelling before going live.
            </p>
        </article>
    </section>

    <section class="section about">
        <h2>About Fundify</h2>
        <p>
            Fundify connects bold local businesses with community investors.
            We focus on transparency: real targets, clear timelines, and
            profit-sharing that everyone understands. Whether you’re launching
            a product or backing one, Fundify keeps the journey simple and fair.
        </p>
    </section>

    <?php include 'footer.php'; ?>

    <script src="Script.js"></script>

    <!--chnages the colour of the nav links if the hamburger menu is toggled-->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('menuToggled', (e) => {
                const isOpen = e.detail.isOpen;
                const navLinks = document.querySelectorAll('.nav-links');

                navLinks.forEach(link => {
                    link.style.setProperty('color', isOpen ? '#000' : '#fff', 'important');
                });
            });
        });

    </script>
</body>

</html>
