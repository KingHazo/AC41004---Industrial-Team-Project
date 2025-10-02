<?php

$host = "database.crwckgs6iy7z.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "admin1234";
$database = "AC41004_DB";

$mysql = null; 

try {
    $mysql = new PDO("mysql:host=".$host.";dbname=".$database, $username, $password);

    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("FATAL DB CONNECTION FAILURE: " . $e->getMessage());
}