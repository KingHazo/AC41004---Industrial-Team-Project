<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Login</title>
    <link rel="stylesheet" href="/login/login.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div id="navbar-placeholder"></div>
    <main>
        <form action="login.php" method="post" class="login-form">
            <h2>Business Owner Login</h2>
            <?php
            // Display an error message if the URL contains a specific error parameter
            if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
                echo "<p style='color:red;'>Invalid email or password.</p>";
            }
            ?>
            <label for="email">Email/Username</label>
            <input id="email" name="email" class="email" type="text" placeholder="Enter email/Username" required>

            <label for="password">Password</label>
            <input id="password" name="password" class="password" type="password" placeholder="Enter password" required>

            <input type="hidden" name="login_type" value="business">

            <button id="login" type="submit">Login</button>

            <div class="login-options">
                <a href="#">Forgot Password?</a>
                <span></span>
                <a href="signup-business.html">Don’t have an account? Sign up</a>
            </div>
        </form>
    </main>
    <div id="footer-placeholder"></div>
</body>
<script src="../load-footer.js"></script>
<script src="../load-navbar.js"></script>
</html>