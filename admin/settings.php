<?php

session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

$admin_id = $_SESSION["admin_id"] ?? "admin";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Settings - NepalJobs</title>

    <link rel="stylesheet" href="admin.css">

</head>

<body>

<div class="admin-container">

    <aside class="admin-sidebar">

        <h2>NepalJobs</h2>

        <p class="admin-title">Admin Panel</p>

        <ul>

            <li>
                <a href="admin.php">Dashboard</a>
            </li>

            <li>
                <a href="users.php">Manage Users</a>
            </li>

            <li>
                <a href="employers.php">Manage Employers</a>
            </li>

            <li>
                <a href="jobseekers.php">Manage Job Seekers</a>
            </li>

            <li>
                <a href="manage-jobs.php">Manage Jobs</a>
            </li>

            <li>
                <a href="applicants.php">Applications</a>
            </li>

            <li class="active">
                <a href="settings.php">Settings</a>
            </li>

            <li>
                <a href="../index.html">Back to Website</a>
            </li>

            <li>
                <a href="../backend/admin-logout.php">Logout</a>
            </li>

        </ul>

    </aside>


    <main class="admin-main">

        <div class="admin-header">

            <div>

                <h1>Admin Settings</h1>

                <p>Manage administrator settings</p>

            </div>

        </div>


        <section class="admin-section">

            <h2>Administrator Information</h2>

            <p>
                <strong>Admin ID:</strong>
                <?php echo htmlspecialchars($admin_id); ?>
            </p>

            <p>
                <strong>Login Status:</strong>
                Logged In
            </p>

            <br>

            <a href="../backend/admin-logout.php">
                Logout from Admin Panel
            </a>

        </section>

    </main>

</div>

</body>

</html>