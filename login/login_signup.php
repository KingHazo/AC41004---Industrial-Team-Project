<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>"> <!-- handles cache issues -->
    <link rel="stylesheet" href="../navbar.css?v=<?php echo time(); ?>"> <!-- handles cache issues -->
    <link rel="stylesheet" href="../footer.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <main class="form-container">
        <form method="POST" action="login.php" id="mainForm" class="login-form">

            <!-- Business / Investor Toggle -->
            <div class="toggle-container" id="loginToggle">
                <div class="slider"></div>
                <button type="button" class="toggle-option active" data-role="business">Business</button>
                <button type="button" class="toggle-option" data-role="investor">Investor</button>
            </div>

            <!-- Hidden input to track user type -->
            <input type="hidden" name="user_type" id="userType" value="business">

            <!-- Form Title -->
            <h2 id="formTitle">Login</h2>

            <!-- Login / Signup Fields -->
            <div id="nameField" style="display:none;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" placeholder="Enter your name">
            </div>

            <label for="email">Email</label>
            <input id="email" name="email" type="text" placeholder="Enter email / Username" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required>

            <label for="confirmPassword" id="confirmLabel" style="display:none;">Confirm Password</label>
            <input id="confirmPassword" name="confirm_password" type="password"
                   placeholder="Confirm password" style="display:none;">

            <!-- Submit Button -->
            <button type="submit" id="submitButton">Login</button>

            <!-- Alternate Link -->
            <p class="alt-link">
                <a href="#" id="toggleMode">
                    Don’t have an account?
                    <span tabindex="0">Sign up</span>
                </a>
            </p>
        </form>
    </main>

    <?php include '../footer.php'; ?>

    <!-- JS Script -->
    <script src="login_signup.js?v=<?php echo time(); ?>"></script>
</body>

</html>
