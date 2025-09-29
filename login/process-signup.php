<?php session_start();

// Use require_once with full path for reliability
require_once dirname(__DIR__) . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    // FIX 1: Convert email to lowercase to prevent case-insensitivity conflicts
    $email = strtolower(htmlspecialchars(trim($_POST['email'])));
    
    // Using raw input variables as requested
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm-password']));
    
    $signup_type = $_POST['signup_type'];

    // 1. Password mismatch check
    if ($password !== $confirm_password) {
        if ($signup_type == 'investor') {
            header("Location: signup-investor.php?error=passwords_mismatch");
        } else {
            header("Location: signup-business.php?error=passwords_mismatch");
        }
        exit();
    }

    // NOTE: Password Hashing removed as requested. Raw password will be stored.

    try{
        // 2. CRITICAL FIX: UNIVERSAL EMAIL EXISTENCE CHECK
        // Check if the email already exists in EITHER the Investor or the Business table.
        $email_found = false;

        // Check 2a: Is email in Investor table?
        $sql_check_investor = "SELECT 1 FROM Investor WHERE Email = :email LIMIT 1";
        $stmt_check_investor = $mysql->prepare($sql_check_investor);
        $stmt_check_investor->bindParam(':email', $email);
        $stmt_check_investor->execute();

        // FIX: Use fetch() instead of rowCount() for more reliable existence check
        if ($stmt_check_investor->fetch()) {
            $email_found = true;
        }

        // Check 2b: Is email in Business table? (Only check if not found in Investor)
        if (!$email_found) {
            $sql_check_business = "SELECT 1 FROM Business WHERE Email = :email LIMIT 1";
            $stmt_check_business = $mysql->prepare($sql_check_business);
            $stmt_check_business->bindParam(':email', $email);
            $stmt_check_business->execute();

            // FIX: Use fetch() instead of rowCount() for more reliable existence check
            if ($stmt_check_business->fetch()) {
                $email_found = true;
            }
        }

        // If email found in EITHER table, redirect with the error
        if ($email_found) {
            if ($signup_type == 'investor') {
                header("Location: signup-investor.php?error=email_exists");
            } else {
                header("Location: signup-business.php?error=email_exists");
            }
            exit();
        }
        
        // 3. INSERTION LOGIC (Only runs if email is confirmed unique)
        if ($signup_type == 'investor') {
            // insert a new investor
            $sql_insert = "INSERT INTO Investor (Name, Password, Email) VALUES (:name, :password, :email)";
            $stmt_insert = $mysql->prepare($sql_insert);
            $stmt_insert->bindParam(':name', $name);
            $stmt_insert->bindParam(':password', $password); // Using raw password
            $stmt_insert->bindParam(':email', $email); 

            if ($stmt_insert->execute()) {
                $investorID = $mysql->lastInsertId();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $investorID;
                $_SESSION['email'] = $email;
                $_SESSION['user_type'] = 'investor';
                
                // Redirect to the investor portal
                header("Location: ../investorportal/investor-portal-home.php");
                exit();
            } else {
                header("Location: signup-investor.php?error=db_error");
                exit();
            }
        } elseif ($signup_type == 'business') {
            // insert a new business owner
            $sql_insert = "INSERT INTO Business (Name, Password, Email) VALUES (:name, :password, :email)";
            $stmt_insert = $mysql->prepare($sql_insert);
            $stmt_insert->bindParam(':name', $name);
            $stmt_insert->bindParam(':password', $password); // Using raw password
            $stmt_insert->bindParam(':email', $email); 
            
            if ($stmt_insert->execute()) {
                $businessID = $mysql->lastInsertId();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $businessID;
                $_SESSION['email'] = $email;
                $_SESSION['user_type'] = 'business';
                
                // Path corrected to use underscore
                header("Location: ../business_portal/business_dashboard.html");
                exit();
            } else {
                // Error redirect to correct business signup page
                header("Location: signup-business.php?error=db_error");
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Signup Database Crash: " . $e->getMessage());
        header("Location: signup-{$signup_type}.php?error=db_crash");
        exit();
    }
} else {
    // if the request is not a POST request
    header("Location: /login/login.php");
    exit();
}
