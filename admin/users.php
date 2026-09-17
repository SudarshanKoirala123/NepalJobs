<?php

session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

require_once "../backend/db.php";

$result = $conn->query("SELECT id, name, email, role FROM users ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - NepalJobs</title>

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

            <li class="active">
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

            <li>
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

                <h1>Manage Users</h1>

                <p>View all registered NepalJobs users</p>

            </div>

        </div>


        <section class="admin-section">

            <h2>All Users</h2>

            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse:collapse; margin-top:20px;">

                    <thead>

                        <tr>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                ID
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Name
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Email
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Role
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if ($result && $result->num_rows > 0) {

                        while ($user = $result->fetch_assoc()) {

                            echo "<tr>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($user["id"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($user["name"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($user["email"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($user["role"])
                                . "</td>";

                            echo "</tr>";
                        }

                    } else {

                        echo "<tr>";
                        echo "<td colspan='4' style='padding:20px; text-align:center;'>";
                        echo "No users found.";
                        echo "</td>";
                        echo "</tr>";

                    }

                    ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>

</html>