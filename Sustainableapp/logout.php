<?php
session_start();

/* Unset all session variables */
$_SESSION = [];

/* Destroy session */
session_destroy();

/* Redirect to login page */
header("Location: http://localhost/Login/login.html");
exit;
?>