<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/admin-login.html");
    exit();
}

$admin_id = trim($_POST["admin_id"] ?? "");
$admin_password = $_POST["admin_password"] ?? "";

$correct_admin_id = "admin";
$correct_admin_password = "admin123";

if ($admin_id === $correct_admin_id && $admin_password === $correct_admin_password) {

    $_SESSION["admin_logged_in"] = true;
    $_SESSION["admin_id"] = $admin_id;

    header("Location: ../admin/admin.php");
    exit();

} else {

    echo "<h2>Invalid Admin ID or Password</h2>";
    echo "<p>Please check your login details.</p>";
    echo "<a href='../admin/admin-login.html'>Back to Admin Login</a>";

    exit();
}

?>