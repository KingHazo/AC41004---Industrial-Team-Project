<?php session_start();

require_once dirname(__DIR__) . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = strtolower(htmlspecialchars(trim($_POST['email'])));
    
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm-password']));
    
    $signup_type = $_POST['signup_type'];

    if ($password !== $confirm_password) {
        if ($signup_type == 'investor') {
            header("Location: signup-investor.php?error=passwords_mismatch");
        } else {
            header("Location: signup-business.php?error=passwords_mismatch");
        }
        exit();
    }


    try{
        // does email exist?
        $email_found = false;

        // Check 2a: Is email in Investor table?
        $sql_check_investor = "SELECT 1 FROM Investor WHERE Email = :email LIMIT 1";
        $stmt_check_investor = $mysql->prepare($sql_check_investor);
        $stmt_check_investor->bindParam(':email', $email);
        $stmt_check_investor->execute();

        if ($stmt_check_investor->fetch()) {
            $email_found = true;
        }

        if (!$email_found) {
            $sql_check_business = "SELECT 1 FROM Business WHERE Email = :email LIMIT 1";
            $stmt_check_business = $mysql->prepare($sql_check_business);
            $stmt_check_business->bindParam(':email', $email);
            $stmt_check_business->execute();

            if ($stmt_check_business->fetch()) {
                $email_found = true;
            }
        }

        // if email is found
        if ($email_found) {
            if ($signup_type == 'investor') {
                header("Location: signup-investor.php?error=email_exists");
            } else {
                header("Location: signup-business.php?error=email_exists");
            }
            exit();
        }
        
        // insert if no error
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
                
                // redirect to the investor portal
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
                
                header("Location: ../business portal/business_dashboard.html");
                exit();
            } else {
                // redirect to correct business signup page if there is an error
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
    header("Location: /login/login.php");
    exit();
}
