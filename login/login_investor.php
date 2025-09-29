<?php
// Start session at the very beginning
session_start();

//error message if login fails
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
     <?php include '../navbar.php'; ?>
    <main>
        <form action="" class="login-form">
            <h2>Investor Owner Login</h2>

            <label for="email">Email/Username</label>
            <input id="email" class="email" type="text" placeholder="Enter email/Username" required>

            <label for="password">Password</label>
            <input id="password" class="password" type="password" placeholder="Enter password" required>

            <button id="login" type="submit">Login</button>

            <div class="login-options">
                <a href="#">Forgot Password?</a>
                <span></span>
                <a href="signup_investor.php">Don’t have an account? Sign up</a>
            </div>
        </form>

    </main>
     <?php include '../footer.php'; ?>
</body>
<script src="../load_footer.js"></script>
<script src="../load_navbar.js"></script>
<script src="login.js"></script>

</html>