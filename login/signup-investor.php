<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Investor | Fundify</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
     <div id="navbar-placeholder"></div>

    <main class="form-container">
    <form action="process-signup.php" method="post" class="signup-form">
            <h2>Investor Signup</h2>
            <p class="success"></p>

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            <span class="error"></span>

            <label for="email">Email</label>
            <input type="email" id="email" placeholder="Enter your email" required>
            <span class="error"></span>

            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Enter password" required>
            <span class="error"></span>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" placeholder="Confirm Password" required>
            <span class="error"></span>

            <input type="hidden" name="signup_type" value="investor">

            <button type="submit" class="btn">Sign Up</button>
            
            <p class="alt-link">Already have an account? <a href="login-investor.php">Login</a></p>
        </form>
    </main>
    <div id="footer-placeholder"></div>
    <script src="../load-footer.js"></script>
    <script src="../load-navbar.js"></script>
    <!-- <script src="/signup.js"></script> -->
</body>

</html>