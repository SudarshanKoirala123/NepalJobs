```php
<?php

session_start();

// ==============================
// ADMIN LOGIN CHECK
// ==============================

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: ../admin/admin-login.html");
    exit();
}

// ==============================
// DATABASE CONNECTION
// ==============================

require_once "db.php";

// ==============================
// GET TOTAL USERS
// ==============================

$totalUsers = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");

if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = $row["total"];
}

// ==============================
// GET TOTAL EMPLOYERS
// ==============================

$totalEmployers = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'employer'");

if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployers = $row["total"];
}

// ==============================
// GET TOTAL JOB SEEKERS
// ==============================

$totalJobSeekers = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'jobseeker'");

if ($result) {
    $row = $result->fetch_assoc();
    $totalJobSeekers = $row["total"];
}

// ==============================
// GET TOTAL JOBS
// ==============================

$totalJobs = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM jobs");

if ($result) {
    $row = $result->fetch_assoc();
    $totalJobs = $row["total"];
}

// ==============================
// GET TOTAL APPLICATIONS
// ==============================

$totalApplications = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM applications");

if ($result) {
    $row = $result->fetch_assoc();
    $totalApplications = $row["total"];
}

// ==============================
// RETURN DATA AS JSON
// ==============================

header("Content-Type: application/json");

echo json_encode([
    "totalUsers" => $totalUsers,
    "totalEmployers" => $totalEmployers,
    "totalJobSeekers" => $totalJobSeekers,
    "totalJobs" => $totalJobs,
    "totalApplications" => $totalApplications
]);

?>
```
