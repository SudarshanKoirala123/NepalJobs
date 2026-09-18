<?php

session_start();

require_once "../backend/db.php";

// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}


// ========================================
// ONLY EMPLOYERS
// ========================================

if ($_SESSION["role"] !== "employer") {
    header("Location: ../login.html");
    exit;
}

$employer_id = $_SESSION["user_id"];


// ========================================
// COUNT MY JOBS
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM jobs
    WHERE employer_id = ?
");

$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$totalJobs = (int)$row["total"];

$stmt->close();


// ========================================
// COUNT MY APPLICATIONS
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM applications a
    INNER JOIN jobs j
        ON a.job_id = j.id
    WHERE j.employer_id = ?
");

$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$totalApplications = (int)$row["total"];

$stmt->close();


// ========================================
// COUNT PENDING APPLICATIONS
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM applications a
    INNER JOIN jobs j
        ON a.job_id = j.id
    WHERE j.employer_id = ?
    AND a.status = 'Pending'
");

$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$pendingApplications = (int)$row["total"];

$stmt->close();


// ========================================
// GET MY RECENT JOBS
// ========================================

$stmt = $conn->prepare("
    SELECT
        id,
        company,
        job_title,
        location,
        salary
    FROM jobs
    WHERE employer_id = ?
    ORDER BY id DESC
    LIMIT 5
");

$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();

$jobs = [];

while ($row = $result->fetch_assoc()) {

    $jobs[] = $row;

}

$stmt->close();

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Employer Dashboard - NepalJobs</title>

    <link rel="stylesheet" href="employer.css">

</head>

<body>


<!-- ========================================
     SIDEBAR
======================================== -->

<div class="sidebar">

    <div class="logo">

        <h2>NepalJobs</h2>

    </div>


    <ul class="menu">

        <li class="active">

            <a href="dashboard.php">
                Dashboard
            </a>

        </li>


        <li>

            <a href="post-job.php">
                Post Job
            </a>

        </li>


        <li>

            <a href="manage-jobs.php">
                Manage Jobs
            </a>

        </li>


        <li>

            <a href="applicants.php">
                Applicants
            </a>

        </li>


        <li>

            <a href="profile.php">
                My Profile
            </a>

        </li>


        <li>

            <a href="settings.php">
                Settings
            </a>

        </li>


        <li>

            <a href="logout.php">
                Logout
            </a>

        </li>

        <li>
    <a href="../index.html">
        Back to Home
    </a>
</li>

    </ul>

</div>


<!-- ========================================
     MAIN CONTENT
======================================== -->

<div class="main-content">


    <div class="topbar">

        <h1>

            Welcome Employer
            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </h1>


        <div class="profile">

            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </div>

    </div>


    <!-- ========================================
         STATISTICS
    ======================================== -->

    <section class="cards">


        <div class="card">

            <h2>
                <?php echo $totalJobs; ?>
            </h2>

            <p>
                My Jobs
            </p>

        </div>


        <div class="card">

            <h2>
                <?php echo $totalApplications; ?>
            </h2>

            <p>
                Applications
            </p>

        </div>


        <div class="card">

            <h2>
                <?php echo $pendingApplications; ?>
            </h2>

            <p>
                Pending Applications
            </p>

        </div>


    </section>


    <!-- ========================================
         MY RECENT JOBS
    ======================================== -->

    <section class="table-section">

        <h2>
            My Recent Jobs
        </h2>


        <?php if (count($jobs) === 0): ?>

            <p>
                You have not posted any jobs yet.
            </p>


            <br>


            <a href="post-job.php">

                <button type="button">
                    Post Your First Job
                </button>

            </a>


        <?php else: ?>


            <?php foreach ($jobs as $job): ?>


                <div style="
                    border:1px solid #ddd;
                    border-radius:8px;
                    padding:20px;
                    margin-bottom:15px;
                ">


                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $job["job_title"]
                        );
                        ?>

                    </h3>


                    <p>

                        <strong>
                            Company:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $job["company"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Location:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $job["location"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>
                            Salary:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $job["salary"]
                        );
                        ?>

                    </p>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>

    </section>


</div>


</body>

</html>