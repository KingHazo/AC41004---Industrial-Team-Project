<?php
// (like user ID and user type) across multiple pages. 
// must be called at the start
session_start();

include __DIR__ . '/../sql/db.php';

// check database connection
if (!$mysql) {
  die(json_encode(['error' => 'Database connection failed.']));
}

/*
 wrapped the $_SERVER["REQUEST_METHOD"] check with isset() to avoid PHP warnings.
 warning occurs if this script is run outside a web server
*/

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    // gets user type from the toggle (hidden input)
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    $errorMessage = ''; // will hold error message if login fails

    try {
        if ($userType === 'investor') {
            // investor login
            $stmt = $mysql->prepare("SELECT * FROM Investor WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $investor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($investor && password_verify($password, $investor['Password'])) {
                $_SESSION['userId'] = $investor['InvestorID'];
                $_SESSION['userType'] = 'investor';
                $_SESSION['logged_in'] = true;

                // return success
                echo json_encode(['success' => true, 'userType' => 'investor']);
                exit();
            } else {
                $errorMessage = "Invalid email or password";
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
                $_SESSION['logged_in'] = true;

                // return success
                echo json_encode(['success' => true, 'userType' => 'business']);
                exit();
            } else {
                $errorMessage = "Invalid email or password";
            }
        } else {
            $errorMessage = "Invalid user type";
        }
    } catch (PDOException $e) {
        // check for duplicate email error (for signup attempt)
        if ($e->getCode() === '23000') {
            // send specific message for duplicate emails
            echo json_encode(['error' => 'Email already exists']);
            exit();
        } else {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    // no user found or password invalid
    echo json_encode(['error' => $errorMessage]);
    exit();
} else {
    // not a POST request
    echo json_encode(['error' => 'Invalid request']);
    exit();
}
?>
