<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $login_type = $_POST['login_type'];

    $sql_investor = "SELECT * FROM Investor WHERE Email = :email AND Password = :password";
    $sql_business = "SELECT * FROM Business WHERE Email = :email AND Password = :password";

    // ... existing investor check logic ...
    $stmt = $mysql->prepare($sql_investor);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $investor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($investor) {
        session_start();
        $_SESSION['userId'] = $investor['InvestorID'];
        $_SESSION['userType'] = 'investor';
        header("Location: investor-portal-home.php");
        exit();
    }

    // ... existing business check logic ...
    $stmt = $mysql->prepare($sql_business);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $business = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($business) {
        session_start();
        $_SESSION['userID'] = $business['BusinessID'];
        $_SESSION['userType'] = 'business';
        header("Location: businesspage.php");
        exit();
    }

    // Redirect to the correct login page based on the hidden input field
    if ($login_type == 'investor') {
        header("Location: login-investor.php?error=invalid_credentials");
    } else {
        header("Location: login-business.php?error=invalid_credentials");
    }
    exit();
}
?>