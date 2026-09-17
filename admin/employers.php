<?php
session_start();

require_once "../backend/db.php";

// Check admin login
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

// Get all employers
$sql = "SELECT id, name, email, role 
        FROM users 
        WHERE LOWER(role) = 'employer'
        ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Employers - NepalJobs</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body>

<div class="admin-container">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="admin-logo">
            <h2>NepalJobs</h2>
            <p>Admin Panel</p>
        </div>

        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="employers.php">Employers</a></li>
            <li><a href="jobseekers.php">Job Seekers</a></li>
            <li><a href="manage-jobs.php">Manage Jobs</a></li>
            <li><a href="applicants.php">Applications</a></li>
            <li><a href="post-job.html">Post Job</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../backend/admin-logout.php">Logout</a></li>
        </ul>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <div class="admin-header">
            <h1>Registered Employers</h1>
        </div>

        <div class="admin-content">

            <table border="1" cellpadding="12" cellspacing="0" width="100%">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php $count = 1; ?>

                    <?php while ($user = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?php echo $count++; ?></td>

                            <td>
                                <?php echo htmlspecialchars($user["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["role"]); ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4">
                            No registered employers found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

</body>
</html>