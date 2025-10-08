<?php
session_start();
include __DIR__ . '/../sql/db.php';

if (!$mysql) {
  // database connection failed
  die(json_encode(['error' => 'Database connection failed.']));
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
        echo json_encode(['error' => 'Please fill all fields']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['error' => 'Passwords do not match']);
        exit();
    }

    // hashed password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($type === 'business') {
            // insert into Business table
            $stmt = $mysql->prepare("INSERT INTO Business (Name, Email, Password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            $userId = $mysql->lastInsertId();
        } elseif ($type === 'investor') {
            // insert into Investor
            $stmt = $mysql->prepare("INSERT INTO Investor (Name, Email, Password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $userId = $mysql->lastInsertId(); // ✅ Save before inserting into Bank

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
        } else {
            echo json_encode(['error' => 'Invalid user type']);
            exit();
        }

        // set session for auto login
        $_SESSION['userId'] = $userId;
        $_SESSION['userType'] = $type;
        $_SESSION['logged_in'] = true;

        // return success for frontend JS
        echo json_encode(['success' => true, 'userType' => $type]);
        exit();

    } catch (PDOException $e) {
        // checks for duplicate email
        if ($e->getCode() === '23000') {
            echo json_encode(['error' => 'Email already exists']);
        } else {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}
