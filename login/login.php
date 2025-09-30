<?php

// (like user ID and user type) across multiple pages. 
// must be called at the start
session_start();

include __DIR__ . '/../sql/db.php';

/*
 wrapped the $_SERVER["REQUEST_METHOD"] check with isset() to avoid PHP warnings.
  warning occurs if this script is run outside a web server
*/

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    // gets user type from the toggle (hidden input)
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    if ($userType === 'investor') {
        // investor login
        $stmt = $mysql->prepare("SELECT * FROM Investor WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $investor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($investor && password_verify($password, $investor['Password'])) {
            $_SESSION['userId'] = $investor['InvestorID'];
            $_SESSION['userType'] = 'investor';
            header("Location: ../investor_portal/investor_portal_home.php");
            exit();
        }
    } elseif ($userType === 'business') {
        // business login
        $stmt = $mysql->prepare("SELECT * FROM Business WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $business = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($business && password_verify($password, $business['Password'])) {
            $_SESSION['userId'] = $business['BusinessID'];
            $_SESSION['userType'] = 'business';
            header("Location: ../business_portal/business_dashboard.php");
            exit();
        }
    }

    // no user found
    header("Location: ../index.php?error=invalid_credentials");
    exit();
}
?>