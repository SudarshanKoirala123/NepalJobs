<?php

session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

require_once "../backend/db.php";

$result = $conn->query("SELECT * FROM applications ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Applications - NepalJobs</title>

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

            <li class="active">
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

                <h1>Applications</h1>

                <p>View job applications</p>

            </div>

        </div>


        <section class="admin-section">

            <h2>All Applications</h2>

            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse:collapse; margin-top:20px;">

                    <thead>

                        <tr>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                ID
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Job ID
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Applicant ID
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if ($result && $result->num_rows > 0) {

                        while ($application = $result->fetch_assoc()) {

                            echo "<tr>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($application["id"])
                                . "</td>";

                            if (isset($application["job_id"])) {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                    . htmlspecialchars($application["job_id"])
                                    . "</td>";

                            } else {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>-</td>";

                            }

                            if (isset($application["jobseeker_id"])) {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                    . htmlspecialchars($application["jobseeker_id"])
                                    . "</td>";

                            } else {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>-</td>";

                            }

                            if (isset($application["status"])) {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                    . htmlspecialchars($application["status"])
                                    . "</td>";

                            } else {

                                echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>-</td>";

                            }

                            echo "</tr>";

                        }

                    } else {

                        echo "<tr>";
                        echo "<td colspan='4' style='padding:20px; text-align:center;'>";
                        echo "No applications found.";
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