<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    header("Location: ../login/login_signup.php");
    exit();
}

include '../sql/db.php';

if (!$mysql) {
    die("Database connection failed.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pitchId'])) {
    $pitchId = (int)$_POST['pitchId'];

    $stmt = $mysql->prepare("
        UPDATE Pitch 
        SET Status = 'active'
        WHERE PitchID = :pitchId AND BusinessID = :businessId AND Status = 'draft'
    ");
    $stmt->bindParam(':pitchId', $pitchId, PDO::PARAM_INT);
    $stmt->bindParam(':businessId', $_SESSION['userId'], PDO::PARAM_INT);
    $stmt->execute();

    //if ($stmt->rowCount() > 0) {
    //echo "Pitch status updated to ACTIVE! Redirecting...";
    //header("Refresh:2; url=pitch_details.php?id=" . $pitchId . "&msg=submitted");
    //exit();
}


    if ($stmt->rowCount() > 0) {
        header("Location: pitch_details.php?id=" . $pitchId . "&msg=submitted");
        exit();
    } else {
        header("Location: pitch_details.php?id=" . $pitchId . "&error=submit_failed");
        exit();
    }

?>