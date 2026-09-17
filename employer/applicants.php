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
// UPDATE APPLICATION STATUS
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = intval($_POST["application_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    $allowedStatuses = [
        "Pending",
        "Shortlisted",
        "Selected",
        "Rejected"
    ];

    if (
        $application_id > 0 &&
        in_array($status, $allowedStatuses, true)
    ) {

        $update = $conn->prepare("
            UPDATE applications a

            INNER JOIN jobs j
                ON a.job_id = j.id

            SET a.status = ?

            WHERE a.id = ?
            AND j.employer_id = ?
        ");

        $update->bind_param(
            "sii",
            $status,
            $application_id,
            $_SESSION["user_id"]
        );

        if ($update->execute()) {

            $message = "Application status updated successfully.";

        } else {

            $message = "Failed to update application status.";

        }

        $update->close();

    } else {

        $message = "Invalid application status.";

    }
}


// ========================================
// GET APPLICANTS
// ========================================

$sql = "
    SELECT
        a.id,
        a.applicant_name,
        a.applicant_email,
        a.applicant_phone,
        a.applicant_message,
        a.status,
        a.applied_at,

        j.job_title,
        j.company,
        j.location

    FROM applications a

    INNER JOIN jobs j
        ON a.job_id = j.id

    WHERE j.employer_id = ?

    ORDER BY a.applied_at DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $_SESSION["user_id"]
);

$stmt->execute();

$result = $stmt->get_result();

$applicants = [];

while ($row = $result->fetch_assoc()) {

    $applicants[] = $row;

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

    <title>Applicants - NepalJobs</title>

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

        <li>
            <a href="manage-jobs.php">Manage Jobs</a>
        </li>

        <li class="active">
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

        <h1>Applicants</h1>

        <div class="profile">
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </div>

    </div>


    <section class="table-section">

        <h2>Job Applicants</h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                background:#eee;
            ">

                <?php echo htmlspecialchars($message); ?>

            </p>

        <?php endif; ?>


        <?php if (count($applicants) === 0): ?>

            <p style="padding:30px; text-align:center;">
                No applicants yet.
            </p>


        <?php else: ?>


            <?php foreach ($applicants as $applicant): ?>

                <div style="
                    border:1px solid #ddd;
                    border-radius:8px;
                    padding:20px;
                    margin-bottom:20px;
                ">

                    <h3>
                        <?php echo htmlspecialchars($applicant["applicant_name"]); ?>
                    </h3>


                    <p>
                        <strong>Job:</strong>
                        <?php echo htmlspecialchars($applicant["job_title"]); ?>
                    </p>


                    <p>
                        <strong>Company:</strong>
                        <?php echo htmlspecialchars($applicant["company"]); ?>
                    </p>


                    <p>
                        <strong>Email:</strong>
                        <?php echo htmlspecialchars($applicant["applicant_email"]); ?>
                    </p>


                    <p>
                        <strong>Phone:</strong>
                        <?php echo htmlspecialchars($applicant["applicant_phone"]); ?>
                    </p>


                    <p>
                        <strong>Message:</strong><br>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $applicant["applicant_message"]
                            )
                        );
                        ?>

                    </p>


                    <p>
                        <strong>Applied At:</strong>
                        <?php echo htmlspecialchars($applicant["applied_at"]); ?>
                    </p>


                    <p>
                        <strong>Current Status:</strong>
                        <?php echo htmlspecialchars($applicant["status"]); ?>
                    </p>


                    <form
                        method="POST"
                        action="applicants.php"
                    >

                        <input
                            type="hidden"
                            name="application_id"
                            value="<?php echo (int)$applicant["id"]; ?>"
                        >


                        <select name="status" required>

                            <option value="Pending"
                                <?php if ($applicant["status"] === "Pending") echo "selected"; ?>>
                                Pending
                            </option>

                            <option value="Shortlisted"
                                <?php if ($applicant["status"] === "Shortlisted") echo "selected"; ?>>
                                Shortlisted
                            </option>

                            <option value="Selected"
                                <?php if ($applicant["status"] === "Selected") echo "selected"; ?>>
                                Selected
                            </option>

                            <option value="Rejected"
                                <?php if ($applicant["status"] === "Rejected") echo "selected"; ?>>
                                Rejected
                            </option>

                        </select>


                        <button type="submit">
                            Update Status
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>


        <?php endif; ?>


    </section>

</div>

</body>
</html>