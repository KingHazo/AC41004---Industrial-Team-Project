<?php

$host = "database.crwckgs6iy7z.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "admin1234";
$database = "AC41004_DB";
try {
    $mysql = new PDO("mysql:host=".$host.";dbname=".$database,
    $username, $password);
    // Set the PDO error mode to exception
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Database connection successful! <br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    // Use die() instead of exit() to provide a more explicit error message
    die();
}
