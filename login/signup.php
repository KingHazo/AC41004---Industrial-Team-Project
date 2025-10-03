<?php
session_start();
include __DIR__ . '/../sql/db.php';

// check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['user_type'] ?? ''; // either  busines or investor
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));

    // basic validation
    if (empty($name) || empty($email) || empty($password)) {
        header("Location: signup_{$type}.php?error=Please+fill+all+fields");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: signup_{$type}.php?error=Passwords+do+not+match");
        exit();
    }

    // hashed password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($type === 'business') {
            $stmt = $mysql->prepare("INSERT INTO Business (Name, Email, Password) VALUES (:name, :email, :password)");
        } elseif ($type === 'investor') {
            $stmt = $mysql->prepare("INSERT INTO Investor (Name, Email, Password) VALUES (:name, :email, :password)");
        } else {
            header("Location: signup_business.php?error=Invalid+signup+type");
            exit();
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        // set session for auto login
        $_SESSION['userId'] = $mysql->lastInsertId();
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
        // checks for duplicate email
        if ($e->getCode() === '23000') { // integrity constraint violation
            $errorMessage = "Email+already+exists";
        } else {
            $errorMessage = urlencode($e->getMessage());
        }
        header("Location: signup_{$type}.php?error={$errorMessage}");
        exit();
    }

} else {
    // redirect if accessed directly
    header("Location: signup_business.php");
    exit();
}
