<?php session_start();

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm-password']));
    $signup_type = $_POST['signup_type'];

    // if passwords match
    if ($password !== $confirm_password) {
        if ($signup_type == 'investor') {
            header("Location: signup-investor.php?error=passwords_mismatch");
        } else {
            header("Location: signup-business.php?error=passwords_mismatch");
        }
        exit();
    }

    if ($signup_type == 'investor') {
        // if email already exists in Investor table
        $sql_check = "SELECT Email FROM Investor WHERE Email = :email";
        $stmt_check = $mysql->prepare($sql_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
            header("Location: signup-investor.php?error=email_exists");
            exit();
        }

        // insert a new investor
        $sql_insert = "INSERT INTO Investor (Name, Email, Password) VALUES (:email, :password)";
        $stmt_insert = $mysql->prepare($sql_insert);
        $stmt_insert->bindParam(':name', $name);
        $stmt_insert->bindParam(':email', $email);
        $stmt_insert->bindParam(':password', $password);

        if ($stmt_insert->execute()) {
            // Get the ID of the newly inserted user
            $investorID = $mysql->lastInsertId();

            // Set session variables to log the user in
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $investorID;
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = 'investor';
            
            // Redirect to the investor portal
            header("Location: investor-portal-home.php");
            exit();
        } else {
            header("Location: signup-investor.php?error=db_error");
            exit();
        }
    } elseif ($signup_type == 'business') {
        // if email already exists in Business table
        $sql_check = "SELECT Email FROM Business WHERE Email = :email";
        $stmt_check = $mysql->prepare($sql_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();
        if ($stmt_check->rowCount() > 0) {
            header("Location: signup-business.php?error=email_exists");
            exit();
        }

        // insert a new business owner
        $sql_insert = "INSERT INTO Business (Name, Email, Password) VALUES (:email, :password)";
        $stmt_insert = $mysql->prepare($sql_insert);
        $stmt_insert->bindParam(':name', $name);
        $stmt_insert->bindParam(':email', $email);
        $stmt_insert->bindParam(':password', $password);

        if ($stmt_insert->execute()) {
            // Get the ID of the newly inserted user
            $businessID = $mysql->lastInsertId();

            // Set session variables to log the user in
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $businessID;
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = 'investor';
            
            // Redirect to the business portal
            header("Location: business_dashboard.html");
            exit();
        } else {
            header("Location: signup-investor.php?error=db_error");
            exit();
        }
    }
} else {
    // if the request is not a POST request
    header("Location: /login/login.php");
    exit();
}
?>
