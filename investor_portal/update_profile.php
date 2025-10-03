<?php
// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// logged in as an investor
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'investor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// database connection
include '../sql/db.php'; 

$investorID = $_SESSION['userId'];

// get data from POST request
$address = trim($_POST['address'] ?? '');
$dob = $_POST['dob'] ?? '';
$nationality = trim($_POST['nationality'] ?? '');
$currency = trim($_POST['currency'] ?? '');

// the sql update statement
$sql = "UPDATE Investor SET 
            Address = :address, 
            DateOfBirth = :dob, 
            Nationality = :nationality, 
            PreferredCurrency = :currency
        WHERE InvestorID = :investorID";

try {
    $stmt = $mysql->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':nationality', $nationality);
    $stmt->bindParam(':currency', $currency);
    $stmt->bindParam(':investorID', $investorID);

    $stmt->execute();

    // check for update
    if ($stmt->rowCount() > 0 || $stmt->errorCode() === '00000') {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        // might mean no change was made
        echo json_encode(['success' => true, 'message' => 'Profile saved (no changes detected).']);
    }

} catch (PDOException $e) {
    error_log("Profile Update Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Could not update profile.']);
}
?>