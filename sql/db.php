<?php

$host = "database-3.ctcgqoecevan.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "admin123";
$database = "database-3";

$mysql = null; 

try {
    $mysql = new PDO("mysql:host=".$host.";dbname=".$database, $username, $password);

    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("FATAL DB CONNECTION FAILURE: " . $e->getMessage());
}
