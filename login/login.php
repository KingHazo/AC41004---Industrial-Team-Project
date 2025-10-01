<?php
session_start();

include '../db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $login_type = $_POST['login_type']; 
    
    try {

        // investor log in
        $sql_investor = "SELECT InvestorID FROM Investor WHERE Email = :email AND Password = :password";
        $stmt_investor = $mysql->prepare($sql_investor);
        $stmt_investor->bindParam(':email', $email);
        $stmt_investor->bindParam(':password', $password); // DIRECT comparison to plain-text DB field
        $stmt_investor->execute();
        $investor = $stmt_investor->fetch(PDO::FETCH_ASSOC);

        // if a matching row is found
        if ($investor) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $investor['InvestorID'];
            $_SESSION['user_type'] = 'investor';
            header("Location: ../investorportal/investor-portal-home.php");
            exit();
        }

        // business log in
        $sql_business = "SELECT BusinessID FROM Business WHERE Email = :email AND Password = :password";
        $stmt_business = $mysql->prepare($sql_business);
        $stmt_business->bindParam(':email', $email);
        $stmt_business->bindParam(':password', $password); // DIRECT comparison to plain-text DB field
        $stmt_business->execute();
        $business = $stmt_business->fetch(PDO::FETCH_ASSOC);

        // if a matching row is found
        if ($business) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $business['BusinessID'];
            $_SESSION['user_type'] = 'business';
            header("Location: ../business portal/business_dashboard.html");
            exit();
        }

        // no user found
        if ($login_type == 'investor') {
            header("Location: login-investor.php?error=invalid_credentials");
        } else {
            header("Location: login-business.php?error=invalid_credentials");
        }
        exit();

    } catch (PDOException $e) {
        error_log("Login Database Crash: " . $e->getMessage());
        // redirect back to log in
        if ($login_type == 'investor') {
            header("Location: login-investor.php?error=db_crash");
        } else {
            header("Location: login-business.php?error=db_crash");
        }
        exit();
    }
} else {
    header("Location: /login/login.php");
    exit();
}
