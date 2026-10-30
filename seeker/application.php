
<?php
session_start();

require_once __DIR__ . "/../backend/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

// Allow only job seekers
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "jobseeker") {
    header("Location: ../login.html");
    exit;
}

$applicant_id = (int) $_SESSION["user_id"];
$applications = [];
$error = "";

// Get applications submitted by the logged-in job seeker
$sql = "
    SELECT
        a.id AS application_id,
        a.job_id,
        a.applicant_name,
        a.applicant_message,
        a.status,
        a.applied_at,
        j.job_title,
        j.company,
        j.location,
        j.salary
    FROM applications AS a
    LEFT JOIN jobs AS j ON a.job_id = j.id
    WHERE a.applicant_id = ?
    ORDER BY a.applied_at DESC, a.id DESC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $applicant_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }

    $stmt->close();
} else {
    $error = "Unable to load your applications. Please check the database configuration.";
}

function safeOutput($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications - NepalJobs</title>

    <link rel="stylesheet" href="seeker.css">

    <style>
        .my-applications-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .applications-subtitle {
            color: #666;
            margin-bottom: 20px;
        }

        .application-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .application-card h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .application-card p {
            margin: 9px 0;
            overflow-wrap: anywhere;
        }

        .application-status {
            display: inline-block;
            padding: 6px 12px;
            background: #eef2ff;
            border-radius: 20px;
            font-weight: bold;
        }

        .application-message {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .applications-notice {
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-top: 20px;
        }

        .applications-link {
            display: inline-block;
            margin: 10px 0 20px;
        }
    </style>
</head>

<body>

<div class="my-applications-container">

    <h1>My Applications</h1>

    <p class="applications-subtitle">
        View the jobs you have applied for and check your application status.
    </p>

    <a class="applications-link" href="dashboard.php">
        Back to Dashboard
    </a>

    <?php if ($error !== ""): ?>

        <div class="applications-notice">
            <?= safeOutput($error) ?>
        </div>

    <?php elseif (empty($applications)): ?>

        <div class="applications-notice">
            <h2>No applications found.</h2>

            <p>
                You have not applied for any jobs using this account yet.
            </p>

            <a href="jobs.php">Find Jobs</a>
        </div>

    <?php else: ?>

        <p>
            <strong>Total applications:</strong>
            <?= count($applications) ?>
        </p>

        <?php foreach ($applications as $application): ?>

            <div class="application-card">

                <h2>
                    <?= safeOutput(
                        $application["job_title"] ?? "Job no longer available"
                    ) ?>
                </h2>

                <p>
                    <strong>Company:</strong>
                    <?= safeOutput($application["company"]) ?>
                </p>

                <p>
                    <strong>Location:</strong>
                    <?= safeOutput($application["location"]) ?>
                </p>

                <p>
                    <strong>Salary:</strong>
                    <?= safeOutput($application["salary"]) ?>
                </p>

                <p>
                    <strong>Application ID:</strong>
                    <?= (int)$application["application_id"] ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <span class="application-status">
                        <?= safeOutput($application["status"]) ?>
                    </span>
                </p>

                <p>
                    <strong>Applied on:</strong>
                    <?= safeOutput($application["applied_at"]) ?>
                </p>

                <?php if (!empty($application["applicant_message"])): ?>
                    <p><strong>Your cover message:</strong></p>

                    <div class="application-message"><?= safeOutput(
                        $application["applicant_message"]
                    ) ?></div>
                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</body>
</html>
