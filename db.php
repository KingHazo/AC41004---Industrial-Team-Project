<?php
$host = "database.crwckgs6iy7z.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "admin1234";
$database = "AC41004_DB"; // Make sure this is the correct database name from your RDS instance
try {
    $mysql = new PDO("mysql:host=".$host.";dbname=".$database,
    $username, $password);
    echo "Database connection successful! <br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit(); // Stop the script from running if the connection fails
}
?>