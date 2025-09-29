<?php
session_start();
include __DIR__ . '/../sql/db.php';

// check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['type'] ?? ''; // either business or investor
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // check password match
    if ($password !== $confirm_password) {
        header("Location: signup_{$type}.php?error=Passwords+do+not+match");
        exit();
    }
    
    // hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($type === 'business') {
            $name = htmlspecialchars(trim($_POST['business_name']));
            $stmt = $mysql->prepare("INSERT INTO Business (Name, Email, Password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
        } elseif ($type === 'investor') {
            $name = htmlspecialchars(trim($_POST['investor_name']));
            $stmt = $mysql->prepare("INSERT INTO Investor (Name, Email, Password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
        } else {
            // invalid type
            header("Location: signup_business.php?error=Invalid+signup+type");
            exit();
        }

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        // set session for auto login
        $_SESSION['userID'] = $mysql->lastInsertId();
        $_SESSION['userType'] = $type;
        $_SESSION['logged_in'] = true;

        // redirect based on type
        if ($type === 'business') {
            header("Location: ../business_portal/business_dashboard.php");
        } else {
            header("Location: ../investor_portal/investor_portal_home.php");
        }
        exit();

    } catch (PDOException $e) {
        header("Location: signup_{$type}.php?error=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    // if someone opens signup.php directly
    header("Location: signup_business.php");
    exit();
}
