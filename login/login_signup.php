<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../navbar.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* toggle slider container */
        .toggle-container {
            width: 220px;
            margin: 20px auto;
            background: #f0f0f0;
            border-radius: 25px;
            display: flex;
            position: relative;
            cursor: pointer;
            border: 5px solid #f0f0f0;
        }

        .toggle-option {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            font-weight: 600;
            z-index: 1;
        }

        .slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: #0b3d91;
            border-radius: 25px;
            transition: left 0.3s;
        }

        .toggle-option.active {
            color: white;
        }
    </style>


</head>

<body>

    <?php include '../navbar.php'; ?>

    <main class="form-container">

        <form method="POST" action="login.php" id="mainForm" class="login-form">
            <!-- business/investor toggle -->
            <div class="toggle-container" id="loginToggle">
                <div class="slider"></div>
                <div class="toggle-option active" data-role="business">Business</div>
                <div class="toggle-option" data-role="investor">Investor</div>
            </div>

            <!-- hidden inputs to track user type -->
            <input type="hidden" name="user_type" id="userType" value="business">

            <!-- form title -->
            <h2 id="formTitle">Login</h2>

            <!-- Login/Signup fields -->
            <div id="nameField" style="display:none;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" placeholder="Enter your name">
            </div>

            <label for="email">Email</label>
            <input id="email" name="email" type="text" placeholder="Enter email/Username" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required>

            <label for="confirmPassword" id="confirmLabel" style="display:none;">Confirm Password</label>
            <input id="confirmPassword" name="confirm_password" type="password" placeholder="Confirm password"
                style="display:none;">

            <button type="submit" id="submitButton">Login</button>
            
            <p class="alt-link">
                <a href="#" id="toggleMode">Don’t have an account? Sign up</a>
            </p>
        </form>

    </main>

    <?php include '../footer.php'; ?>
    <script src="login_signup.js"></script>
</body>

</html>