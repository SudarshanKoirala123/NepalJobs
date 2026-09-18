<?php

session_start();

require_once "../backend/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

// Only allow job seekers
if ($_SESSION["role"] !== "jobseeker") {
    header("Location: ../login.html");
    exit;
}


// ========================================
// COUNT AVAILABLE JOBS
// ========================================

$jobQuery = "SELECT COUNT(*) AS total FROM jobs";

$jobResult = $conn->query($jobQuery);

$availableJobs = 0;

if ($jobResult) {
    $jobRow = $jobResult->fetch_assoc();
    $availableJobs = (int)$jobRow["total"];
}


// ========================================
// COUNT APPLICATIONS
// ========================================

$applicationQuery = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE applicant_id = ?
";

$stmt = $conn->prepare($applicationQuery);

$appliedJobs = 0;

if ($stmt) {

    $stmt->bind_param("i", $_SESSION["user_id"]);

    $stmt->execute();

    $applicationResult = $stmt->get_result();

    if ($applicationResult) {

        $applicationRow = $applicationResult->fetch_assoc();

        $appliedJobs = (int)$applicationRow["total"];
    }

    $stmt->close();
}


// ========================================
// CLOSE DATABASE
// ========================================

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Job Seeker Dashboard - NepalJobs</title>

    <link rel="stylesheet" href="seeker.css">

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
                <span>Dashboard</span>
            </a>

        </li>


        <li>

            <a href="jobs.php">
                <span>Find Jobs</span>
            </a>

        </li>


        <li>

            <a href="application.php">
                <span>My Applications</span>
            </a>

        </li>


        <li>

            <a href="profile.php">
                <span>My Profile</span>
            </a>

        </li>


        <li>

            <a href="settings.php">
                <span>Settings</span>
            </a>

        </li>


        <li>

            <a href="logout.php">
                <span>Logout</span>
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

            Welcome Job Seeker
            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </h1>


        <div class="profile">

            <span>

                <?php echo htmlspecialchars($_SESSION["name"]); ?>

            </span>

        </div>

    </div>


    <!-- ========================================
         STATISTICS
    ======================================== -->

    <section class="cards">


        <div class="card">

            <h2>
                <?php echo $availableJobs; ?>
            </h2>

            <p>
                Available Jobs
            </p>

        </div>


        <div class="card">

            <h2>
                <?php echo $appliedJobs; ?>
            </h2>

            <p>
                Applied Jobs
            </p>

        </div>


        <div class="card">

            <h2>
                0
            </h2>

            <p>
                Saved Jobs
            </p>

        </div>


        <div class="card">

            <h2>
                0
            </h2>

            <p>
                Selected Jobs
            </p>

        </div>


    </section>


    <!-- ========================================
         RECOMMENDED JOBS
    ======================================== -->

    <section class="table-section">

        <h2>
            Recommended Jobs
        </h2>


        <p style="margin-bottom:20px;">

            You currently have

            <strong>
                <?php echo $availableJobs; ?>
            </strong>

            available job(s).

        </p>


        <a href="jobs.php">

            <button type="button">
                Find Available Jobs
            </button>

        </a>

    </section>


</div>

</body>

</html>