<?php

session_start();

// Remove all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Return to admin login
header("Location: ../admin/admin-login.html");
exit();

?>