<?php

session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin-login.html");
    exit();
}

require_once "../backend/db.php";

$result = $conn->query("
    SELECT id, employer_id, company, job_title, location, salary, description
    FROM jobs
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Jobs - NepalJobs</title>

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

            <li class="active">
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

                <h1>Manage Jobs</h1>

                <p>View jobs posted on NepalJobs</p>

            </div>

        </div>


        <section class="admin-section">

            <h2>All Jobs</h2>

            <div style="overflow-x:auto;">

                <table style="width:100%; border-collapse:collapse; margin-top:20px;">

                    <thead>

                        <tr>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                ID
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Company
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Job Title
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Location
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Salary
                            </th>

                            <th style="padding:12px; border-bottom:1px solid #ddd;">
                                Employer ID
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if ($result && $result->num_rows > 0) {

                        while ($job = $result->fetch_assoc()) {

                            echo "<tr>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["id"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["company"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["job_title"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["location"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["salary"])
                                . "</td>";

                            echo "<td style='padding:12px; border-bottom:1px solid #ddd;'>"
                                . htmlspecialchars($job["employer_id"])
                                . "</td>";

                            echo "</tr>";

                        }

                    } else {

                        echo "<tr>";
                        echo "<td colspan='6' style='padding:20px; text-align:center;'>";
                        echo "No jobs found.";
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