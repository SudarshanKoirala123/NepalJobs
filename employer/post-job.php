<?php

session_start();

require_once "../backend/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

if ($_SESSION["role"] !== "employer") {
    header("Location: ../login.html");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company = trim($_POST["company"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salary = trim($_POST["salary"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (
        $company === "" ||
        $title === "" ||
        $location === "" ||
        $salary === "" ||
        $description === ""
    ) {

        $message = "Please fill in all fields.";

    } else {

        // Prevent duplicate jobs

        $check = $conn->prepare("
            SELECT id
            FROM jobs
            WHERE employer_id = ?
            AND company = ?
            AND job_title = ?
            AND location = ?
        ");

        $check->bind_param(
            "isss",
            $_SESSION["user_id"],
            $company,
            $title,
            $location
        );

        $check->execute();

        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {

            $message = "You have already posted this job.";

        } else {

            $insert = $conn->prepare("
                INSERT INTO jobs
                (
                    employer_id,
                    company,
                    job_title,
                    location,
                    salary,
                    description
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "isssss",
                $_SESSION["user_id"],
                $company,
                $title,
                $location,
                $salary,
                $description
            );

            if ($insert->execute()) {

                $message = "Job posted successfully!";

            } else {

                $message = "Failed to post job: " . $insert->error;

            }

            $insert->close();
        }

        $check->close();
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Post Job - NepalJobs</title>

    <link rel="stylesheet" href="employer.css">

</head>

<body>

<div class="sidebar">

    <div class="logo">
        <h2>NepalJobs</h2>
    </div>

    <ul class="menu">

        <li>
            <a href="dashboard.php">Dashboard</a>
        </li>

        <li class="active">
            <a href="post-job.php">Post Job</a>
        </li>

        <li>
            <a href="manage-jobs.php">Manage Jobs</a>
        </li>

        <li>
            <a href="applicants.php">Applicants</a>
        </li>

        <li>
            <a href="profile.php">My Profile</a>
        </li>

        <li>
            <a href="settings.php">Settings</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <h1>Post a Job</h1>

        <div class="profile">

            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </div>

    </div>


    <section class="table-section">

        <h2>Create Job Posting</h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                border-radius:6px;
                background:#eee;
            ">

                <?php echo htmlspecialchars($message); ?>

            </p>

        <?php endif; ?>


        <form method="POST" action="post-job.php">


            <div style="margin-bottom:15px;">

                <label>Company Name</label>

                <input
                    type="text"
                    name="company"
                    placeholder="Enter company name"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Job Title</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Enter job title"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Location</label>

                <input
                    type="text"
                    name="location"
                    placeholder="Kathmandu"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Salary</label>

                <input
                    type="text"
                    name="salary"
                    placeholder="Rs. 40,000 - 60,000"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Description</label>

                <textarea
                    name="description"
                    rows="7"
                    placeholder="Describe the job..."
                    required
                ></textarea>

            </div>


            <button type="submit">
                Post Job
            </button>


        </form>

    </section>

</div>

</body>
</html>