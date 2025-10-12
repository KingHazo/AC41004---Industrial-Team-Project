<?php
session_start();

// unset all session variables
$_SESSION = [];

// destroy the session
session_destroy();

// redirect to homepage or login page
header("Location: /index.php");
exit();
