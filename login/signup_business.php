<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Business Owner | Fundify</title>
    <link rel="stylesheet" href="../Style.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include '../navbar.php'; ?>
    <main class="form-container">
        <form class="signup-form" method="POST" action="signup.php">
            <input type="hidden" name="type" value="business">
            <h2>Business Owner Signup</h2>
            <p class="success"></p>

            <label for="business-name">Business Name</label>
            <input type="text" id="business-name" name="business_name" placeholder="Enter your business name" required>
            <span class="error"></span>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <span class="error"></span>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
            <span class="error"></span>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password"
                required>
            <span class="error"></span>

            <button type="submit" class="btn">Sign Up</button>
            <p class="alt-link">Already have an account? <a href="login_business.php">Login</a></p>
        </form>

    </main>
    <?php include '../footer.php'; ?>
    <script src="../load_footer.js"></script>
    <script src="../load_navbar.js"></script>
    <script src="/signup.js"></script>
</body>

</html>