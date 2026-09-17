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
// ONLY JOB SEEKERS
// ========================================

if ($_SESSION["role"] !== "jobseeker") {
    header("Location: ../login.html");
    exit;
}


// ========================================
// GET JOBS FROM DATABASE
// ========================================

$sql = "
    SELECT
        id,
        company,
        job_title,
        location,
        salary,
        description
    FROM jobs
    ORDER BY id DESC
";

$result = $conn->query($sql);

$jobs = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $jobs[] = $row;

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Find Jobs - NepalJobs</title>

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


        <li>

            <a href="dashboard.php">

                <span>Dashboard</span>

            </a>

        </li>


        <li class="active">

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


    </ul>

</div>


<!-- ========================================
     MAIN CONTENT
======================================== -->

<div class="main-content">


    <!-- TOPBAR -->

    <div class="topbar">

        <h1>
            Find Jobs
        </h1>


        <div class="profile">

            <span>

                <?php
                echo htmlspecialchars($_SESSION["name"]);
                ?>

            </span>

        </div>

    </div>



    <!-- ========================================
         SEARCH
    ======================================== -->

    <section class="table-section">

        <h2>
            Search Jobs
        </h2>


        <input
            type="text"
            id="searchInput"
            placeholder="Search by job title, company or location..."
        >

    </section>



    <!-- ========================================
         JOB LIST
    ======================================== -->

    <section
        class="table-section"
        style="margin-top:25px;"
    >

        <h2>
            Available Jobs
        </h2>


        <div id="jobsContainer">


            <?php if (count($jobs) === 0): ?>


                <p style="text-align:center; padding:30px;">

                    No jobs are currently available.

                </p>


            <?php else: ?>


                <?php foreach ($jobs as $job): ?>


                    <div
                        class="job-box"
                        data-title="<?php echo htmlspecialchars(strtolower($job["job_title"])); ?>"
                        data-company="<?php echo htmlspecialchars(strtolower($job["company"])); ?>"
                        data-location="<?php echo htmlspecialchars(strtolower($job["location"])); ?>"
                        style="
                            border:1px solid #ddd;
                            border-radius:8px;
                            padding:20px;
                            margin-bottom:15px;
                        "
                    >


                        <h3>

                            <?php
                            echo htmlspecialchars($job["job_title"]);
                            ?>

                        </h3>


                        <p>

                            <strong>
                                Company:
                            </strong>

                            <?php
                            echo htmlspecialchars($job["company"]);
                            ?>

                        </p>


                        <p>

                            <strong>
                                Location:
                            </strong>

                            <?php
                            echo htmlspecialchars($job["location"]);
                            ?>

                        </p>


                        <p>

                            <strong>
                                Salary:
                            </strong>

                            <?php
                            echo htmlspecialchars($job["salary"]);
                            ?>

                        </p>


                        <p>

                            <strong>
                                Description:
                            </strong>

                            <br>

                            <?php
                            echo nl2br(
                                htmlspecialchars($job["description"])
                            );
                            ?>

                        </p>


                        <!-- APPLY BUTTON -->

                        <div style="margin-top:15px;">

                            <a
                                href="application.php?job_id=<?php echo (int)$job["id"]; ?>"
                            >

                                <button type="button">
                                    Apply
                                </button>

                            </a>

                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </div>

    </section>


</div>



<!-- ========================================
     SEARCH SCRIPT
======================================== -->

<script>

const searchInput = document.getElementById("searchInput");

const jobBoxes = document.querySelectorAll(".job-box");


searchInput.addEventListener("input", function () {

    const search = this.value.trim().toLowerCase();


    jobBoxes.forEach(function (job) {

        const title = job.dataset.title;
        const company = job.dataset.company;
        const location = job.dataset.location;


        if (
            title.includes(search) ||
            company.includes(search) ||
            location.includes(search)
        ) {

            job.style.display = "block";

        } else {

            job.style.display = "none";

        }

    });

});

</script>


</body>

</html>