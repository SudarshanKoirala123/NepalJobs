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


// ========================================
// DELETE JOB
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $job_id = intval($_POST["job_id"] ?? 0);

    if ($job_id > 0) {

        // Make sure job belongs to this employer

        $check = $conn->prepare("
            SELECT id
            FROM jobs
            WHERE id = ?
            AND employer_id = ?
        ");

        $check->bind_param(
            "ii",
            $job_id,
            $_SESSION["user_id"]
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $delete = $conn->prepare("
                DELETE FROM jobs
                WHERE id = ?
                AND employer_id = ?
            ");

            $delete->bind_param(
                "ii",
                $job_id,
                $_SESSION["user_id"]
            );

            if ($delete->execute()) {

                $message = "Job deleted successfully.";

            } else {

                $message = "Failed to delete job.";

            }

            $delete->close();

        } else {

            $message = "You cannot delete this job.";

        }

        $check->close();
    }
}


// ========================================
// GET MY JOBS
// ========================================

$stmt = $conn->prepare("
    SELECT
        id,
        company,
        job_title,
        location,
        salary,
        description
    FROM jobs
    WHERE employer_id = ?
    ORDER BY id DESC
");

$stmt->bind_param(
    "i",
    $_SESSION["user_id"]
);

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

    <title>Manage Jobs - NepalJobs</title>

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

        <li>
            <a href="post-job.php">Post Job</a>
        </li>

        <li class="active">
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

        <h1>Manage Jobs</h1>

        <div class="profile">
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </div>

    </div>


    <section class="table-section">

        <h2>My Jobs</h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                background:#eee;
            ">

                <?php echo htmlspecialchars($message); ?>

            </p>

        <?php endif; ?>


        <?php if (count($jobs) === 0): ?>

            <p>
                You have not posted any jobs yet.
            </p>


        <?php else: ?>


            <?php foreach ($jobs as $job): ?>

                <div style="
                    border:1px solid #ddd;
                    border-radius:8px;
                    padding:20px;
                    margin-bottom:20px;
                ">

                    <h3>
                        <?php echo htmlspecialchars($job["job_title"]); ?>
                    </h3>

                    <p>
                        <strong>Company:</strong>
                        <?php echo htmlspecialchars($job["company"]); ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?php echo htmlspecialchars($job["location"]); ?>
                    </p>

                    <p>
                        <strong>Salary:</strong>
                        <?php echo htmlspecialchars($job["salary"]); ?>
                    </p>

                    <p>
                        <strong>Description:</strong><br>
                        <?php
                        echo nl2br(
                            htmlspecialchars($job["description"])
                        );
                        ?>
                    </p>


                    <form
                        method="POST"
                        action="manage-jobs.php"
                        onsubmit="return confirm('Are you sure you want to delete this job?');"
                    >

                        <input
                            type="hidden"
                            name="job_id"
                            value="<?php echo (int)$job["id"]; ?>"
                        >

                        <button type="submit">
                            Delete Job
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>


        <?php endif; ?>


    </section>

</div>

</body>
</html>