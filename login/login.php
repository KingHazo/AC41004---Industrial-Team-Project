<?php
session_start();

// Ensure the path is correct: includes a file one directory up
include '../db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    // NOTE: Storing and comparing plain-text passwords is highly insecure.
    // This method is used here ONLY to match your current database configuration for your school project.
    $password = htmlspecialchars(trim($_POST['password']));
    $login_type = $_POST['login_type']; 
    
    // START TRY BLOCK to catch database connection/query errors (500 errors)
    try {

        // --- INVESTOR LOGIN CHECK ---
        // Query to check Email AND plain-text Password simultaneously
        $sql_investor = "SELECT InvestorID FROM Investor WHERE Email = :email AND Password = :password";
        $stmt_investor = $mysql->prepare($sql_investor);
        $stmt_investor->bindParam(':email', $email);
        $stmt_investor->bindParam(':password', $password); // DIRECT comparison to plain-text DB field
        $stmt_investor->execute();
        $investor = $stmt_investor->fetch(PDO::FETCH_ASSOC);

        // If a matching row is found (meaning email and plain-text password are correct)
        if ($investor) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $investor['InvestorID'];
            $_SESSION['user_type'] = 'investor';
            header("Location: ../investor portal/investor-portal-home.php");
            exit();
        }

        // --- BUSINESS LOGIN CHECK ---
        // Query to check Email AND plain-text Password simultaneously
        $sql_business = "SELECT BusinessID FROM Business WHERE Email = :email AND Password = :password";
        $stmt_business = $mysql->prepare($sql_business);
        $stmt_business->bindParam(':email', $email);
        $stmt_business->bindParam(':password', $password); // DIRECT comparison to plain-text DB field
        $stmt_business->execute();
        $business = $stmt_business->fetch(PDO::FETCH_ASSOC);

        // If a matching row is found (meaning email and plain-text password are correct)
        if ($business) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $business['BusinessID'];
            $_SESSION['user_type'] = 'business';
            header("Location: ../business portal/business_dashboard.html");
            exit();
        }

        // If no user found in either table (standard login failure), redirect with 'invalid_credentials' error
        if ($login_type == 'investor') {
            header("Location: login-investor.php?error=invalid_credentials");
        } else {
            header("Location: login-business.php?error=invalid_credentials");
        }
        exit();

    } catch (PDOException $e) {
        // Log the detailed error to the server's error log for debugging
        error_log("Login Database Crash: " . $e->getMessage());

        // Redirect user back to the correct login page with a specific error
        if ($login_type == 'investor') {
            header("Location: login-investor.php?error=db_crash");
        } else {
            header("Location: login-business.php?error=db_crash");
        }
        exit();
    }
} else {
    // If the request is not a POST request, redirect to the main login page
    header("Location: /login/login.php");
    exit();
}
