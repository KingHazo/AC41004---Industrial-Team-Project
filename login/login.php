<?php

// (like user ID and user type) across multiple pages. 
// must be called at the start
session_start();

include __DIR__ . '/../sql/db.php';

/*
 wrapped the $_SERVER["REQUEST_METHOD"] check with isset() to avoid PHP warnings.
  warning occurs if this script is run outside a web server
*/

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    $sql_investor = "SELECT * FROM Investor WHERE Email = :email AND Password = :password";
    $sql_business = "SELECT * FROM Business WHERE Email = :email AND Password = :password";

    // check investor table first
    $stmt = $mysql->prepare($sql_investor);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $investor = $stmt->fetch(PDO::FETCH_ASSOC);

    // if a user is found in the investor table
    if ($investor) {
       //session_start();
        $_SESSION['userId'] = $investor['InvestorID']; // Store user ID
        $_SESSION['userType'] = 'investor'; // Store user type
        // Redirect to a dashboard or profile page
        header("Location: /../investor%20portal/investor_portal_home.php");
        exit();
    }

    // check business acount if no investor found
    $stmt = $mysql->prepare($sql_business);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $business = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($business) {
        //session_start();
        $_SESSION['userID'] = $business['BusinessID'];
        $_SESSION['userType'] = 'business';
        header("Location: ../business_portal/business_dashboard.php");
        exit();
    }

    // send an error message to log in page
    header("Location: login.html?error=invalid_credentials");
    exit();
}
?>