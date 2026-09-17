<?php

session_start();

// ==============================
// ADMIN LOGIN CHECK
// ==============================

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

// ==============================
// DATABASE CONNECTION
// ==============================

require_once "../backend/db.php";

// ==============================
// DEFAULT VALUES
// ==============================

$totalUsers = 0;
$totalEmployers = 0;
$totalJobSeekers = 0;
$totalJobs = 0;
$totalApplications = 0;

$dbError = "";

// ==============================
// TOTAL USERS
// ==============================

$result = $conn->query("SELECT COUNT(*) AS total FROM users");

if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = $row["total"];
} else {
    $dbError .= "Users query failed: " . $conn->error . "<br>";
}

// ==============================
// TOTAL EMPLOYERS
// ==============================

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'employer'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployers = $row["total"];
} else {
    $dbError .= "Employers query failed: " . $conn->error . "<br>";
}

// ==============================
// TOTAL JOB SEEKERS
// ==============================

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'jobseeker'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalJobSeekers = $row["total"];
} else {
    $dbError .= "Job seekers query failed: " . $conn->error . "<br>";
}

// ==============================
// TOTAL JOBS
// ==============================

$result = $conn->query("SELECT COUNT(*) AS total FROM jobs");

if ($result) {
    $row = $result->fetch_assoc();
    $totalJobs = $row["total"];
} else {
    $dbError .= "Jobs query failed: " . $conn->error . "<br>";
}

// ==============================
// TOTAL APPLICATIONS
// ==============================

$result = $conn->query("SELECT COUNT(*) AS total FROM applications");

if ($result) {
    $row = $result->fetch_assoc();
    $totalApplications = $row["total"];
} else {
    $dbError .= "Applications query failed: " . $conn->error . "<br>";
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard - NepalJobs</title>

<link rel="stylesheet" href="admin.css">
```

</head>

<body>

<div class="admin-container">

```
<!-- ==============================
     SIDEBAR
=============================== -->

<aside class="admin-sidebar">

    <div class="admin-logo">

        <h2>NepalJobs</h2>

        <p>Admin Panel</p>

    </div>

    <ul>

        <li>
            <a href="admin.php">
                Dashboard
            </a>
        </li>

        <li>
            <a href="users.php">
                Manage Users
            </a>
        </li>

        <li>
            <a href="employers.php">
                Employers
            </a>
        </li>

        <li>
            <a href="jobseekers.php">
                Job Seekers
            </a>
        </li>

        <li>
            <a href="manage-jobs.php">
                Manage Jobs
            </a>
        </li>

        <li>
            <a href="applicants.php">
                Applications
            </a>
        </li>

        <li>
            <a href="post-job.html">
                Post Job
            </a>
        </li>

        <li>
            <a href="settings.php">
                Settings
            </a>
        </li>

        <li>
            <a href="../backend/admin-logout.php">
                Logout
            </a>
        </li>

    </ul>

</aside>


<!-- ==============================
     MAIN CONTENT
=============================== -->

<main class="admin-main">

    <div class="admin-header">

        <div>

            <h1>Admin Dashboard</h1>

            <p>
                Welcome to NepalJobs Administration Panel
            </p>

        </div>

    </div>


    <!-- ==============================
         DATABASE ERROR
    =============================== -->

    <?php if ($dbError != ""): ?>

        <div style="
            background:#ffe5e5;
            color:#b00020;
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
            border:1px solid #ffaaaa;
        ">

            <strong>Database Error:</strong>

            <br><br>

            <?php echo $dbError; ?>

        </div>

    <?php endif; ?>


    <!-- ==============================
         DASHBOARD CARDS
    =============================== -->

    <div class="admin-cards">

        <div class="admin-card">

            <h3>Total Users</h3>

            <p>
                <?php echo $totalUsers; ?>
            </p>

        </div>


        <div class="admin-card">

            <h3>Employers</h3>

            <p>
                <?php echo $totalEmployers; ?>
            </p>

        </div>


        <div class="admin-card">

            <h3>Job Seekers</h3>

            <p>
                <?php echo $totalJobSeekers; ?>
            </p>

        </div>


        <div class="admin-card">

            <h3>Total Jobs</h3>

            <p>
                <?php echo $totalJobs; ?>
            </p>

        </div>


        <div class="admin-card">

            <h3>Total Applications</h3>

            <p>
                <?php echo $totalApplications; ?>
            </p>

        </div>

    </div>


    <!-- ==============================
         RECENT ACTIVITY
    =============================== -->

    <section class="admin-section">

        <h2>Recent Activity</h2>

        <p>
            Admin activity and system information
            will appear here.
        </p>

    </section>

</main>


</div>

</body>

</html>
