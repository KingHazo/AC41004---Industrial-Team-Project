<?php
$host = "database-3.ctcgqoecevan.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "admin123";
$database = "database-3"; 
try {
    $mysql = new PDO("mysql:host=".$host.";dbname=".$database,
    $username, $password);
    // echo "Database connection successful! <br>";
} catch (PDOException $e) {
    // echo "Database connection failed: " . $e->getMessage();
    exit(); 
}
?>