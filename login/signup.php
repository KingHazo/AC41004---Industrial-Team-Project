<?php
session_start();
include __DIR__ . '/../sql/db.php';

if (!$mysql) {
  die("Database connection failed.");
}



// check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['user_type'] ?? ''; // either business or investor
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
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
           } elseif ($type === 'investor') {
            // insert into Investor
            $stmt = $mysql->prepare("INSERT INTO Investor (Name, Email, Password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $investorId = $mysql->lastInsertId(); // ✅ Save before inserting into Bank

            // generate mock bank account details
            $accountNumber = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $holderName = $name;
            $initialBalance = 50000;

            // insert into Bank table
            $bankStmt = $mysql->prepare("
                INSERT INTO Bank (AccountNumber, HolderName, Balance)
                VALUES (:accountNumber, :holderName, :balance)
            ");
            $bankStmt->bindParam(':accountNumber', $accountNumber);
            $bankStmt->bindParam(':holderName', $holderName);
            $bankStmt->bindParam(':balance', $initialBalance);
            $bankStmt->execute();

            // set correct session data
            $_SESSION['userId'] = $investorId;
            $_SESSION['userType'] = 'investor';
            $_SESSION['logged_in'] = true;

            header("Location: ../investor_portal/investor_portal_home.php");
            exit();
           }

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
        if ($e->getCode() === '23000') {
            header("Location: signup_{$type}.php?error=email_exists");
        } else {
            header("Location: signup_{$type}.php?error=" . urlencode($e->getMessage()));
        }
        exit();
    }
} else {
    header("Location: signup_business.php");
    exit();
}
