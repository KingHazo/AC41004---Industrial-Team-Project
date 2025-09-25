<?php

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
        session_start();
        $_SESSION['userId'] = $investor['InvestorID']; // Store user ID
        $_SESSION['userType'] = 'investor'; // Store user type
        // Redirect to a dashboard or profile page
        header("Location: investorpage.php");
        exit();
    }

    // check business acount if no investor found
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

    echo "Invalid email or password.";
}
?>