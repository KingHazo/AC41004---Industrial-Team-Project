<?php
// (like user ID and user type) across multiple pages. 
// must be called at the start
session_start();

include __DIR__ . '/../sql/db.php';

if (!$mysql) {
  die("Database connection failed.");
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
    }

    // no user found
    echo json_encode(['error' => $errorMessage]);
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
const loginForm = document.getElementById("login-form");

loginForm.addEventListener("submit", async (e) => {
    e.preventDefault(); // prevent normal form submission

    const formData = new FormData(loginForm);

    const response = await fetch("login.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.error) {
        Toastify({
            text: "❌ " + result.error,
            duration: 5000,
            gravity: "top",
            position: "right",
            backgroundColor: "#e74c3c",
            close: true
        }).showToast();
    } else if (result.success) {
        // redirect after successful login
        if (result.userType === 'investor') {
            window.location.href = "../investor_portal/investor_portal_home.php";
        } else {
            window.location.href = "../business_portal/business_dashboard.php";
        }
    }
});
</script>
