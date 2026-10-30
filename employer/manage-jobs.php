
<?php
session_start();

require_once __DIR__ . "/../backend/db.php";

// ========================================
// AUTHENTICATION
// ========================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "employer") {
    header("Location: ../login.html");
    exit;
}

$employer_id = (int) $_SESSION["user_id"];
$message = "";
$message_type = "";

// ========================================
// DELETE JOB
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $job_id = filter_input(
        INPUT_POST,
        "job_id",
        FILTER_VALIDATE_INT
    );

    if (!$job_id || $job_id < 1) {

        $message = "Invalid job selected.";
        $message_type = "error";

    } else {

        // Confirm this job belongs to the logged-in employer.
        $check = $conn->prepare(
            "SELECT id FROM jobs
             WHERE id = ? AND employer_id = ?"
        );

        if (!$check) {
            die("Unable to prepare the job query.");
        }

        $check->bind_param("ii", $job_id, $employer_id);
        $check->execute();

        $check_result = $check->get_result();

        if ($check_result->num_rows === 0) {

            $message = "You cannot delete this job.";
            $message_type = "error";

        } else {

            // Applications may reference this job through a foreign key.
            // The database may prevent deletion while applications exist.
            $delete = $conn->prepare(
                "DELETE FROM jobs
                 WHERE id = ? AND employer_id = ?"
            );

            if (!$delete) {

                $message = "Unable to prepare the delete operation.";
                $message_type = "error";

            } else {

                $delete->bind_param("ii", $job_id, $employer_id);

                try {
                    if ($delete->execute() && $delete->affected_rows > 0) {
                        $message = "Job deleted successfully.";
                        $message_type = "success";
                    } else {
                        $message = "The job was not deleted.";
                        $message_type = "error";
                    }
                } catch (mysqli_sql_exception $e) {
                    $message = "This job could not be deleted. It may have applications linked to it. Keep the job so its application records are preserved.";
                    $message_type = "error";
                }

                $delete->close();
            }
        }

        $check->close();
    }
}

// ========================================
// GET EMPLOYER'S JOBS
// ========================================

$stmt = $conn->prepare(
    "SELECT
        j.id,
        j.company,
        j.job_title,
        j.location,
        j.salary,
        j.description,
        (
            SELECT COUNT(*)
            FROM applications a
            WHERE a.job_id = j.id
        ) AS applicant_count
     FROM jobs j
     WHERE j.employer_id = ?
     ORDER BY j.id DESC"
);

if (!$stmt) {
    die("Unable to load jobs. Please check your database.");
}

$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();
$jobs = [];

while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$stmt->close();

function jobEscape($value) {
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

    <title>Manage Jobs - NepalJobs</title>

    <link rel="stylesheet" href="employer.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --background: #f3f6fb;
            --text: #172033;
            --muted: #64748b;
            --border: #e2e8f0;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 245px;
            overflow-y: auto;
            background: #1e293b;
            color: white;
            z-index: 10;
        }

        .logo {
            padding: 22px 25px;
            background: #172033;
        }

        .logo h2 {
            margin: 0;
            font-size: 26px;
        }

        .menu {
            list-style: none;
            margin: 0;
            padding: 12px 0;
        }

        .menu li a {
            display: block;
            padding: 17px 25px;
            color: #e2e8f0;
            text-decoration: none;
            transition: background .2s;
        }

        .menu li a:hover,
        .menu li.active a {
            background: var(--primary);
            color: white;
        }

        .main-content {
            margin-left: 245px;
            padding: 28px;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            background: white;
            border-radius: 14px;
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        }

        .topbar h1 {
            margin: 0;
            font-size: 28px;
        }

        .profile {
            color: var(--muted);
            font-weight: 600;
        }

        .jobs-section {
            background: white;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: 22px;
        }

        .job-count {
            padding: 7px 12px;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
            gap: 18px;
        }

        .job-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 19px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: white;
            transition: box-shadow .2s, transform .2s;
        }

        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
        }

        .job-card h3 {
            margin: 0 0 8px;
            font-size: 19px;
            overflow-wrap: anywhere;
        }

        .company-name {
            color: var(--muted);
            margin: 0 0 16px;
            overflow-wrap: anywhere;
        }

        .job-details {
            display: grid;
            gap: 9px;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .job-details p {
            margin: 0;
            overflow-wrap: anywhere;
        }

        .job-description {
            padding-top: 12px;
            margin-top: 2px;
            border-top: 1px solid var(--border);
            font-size: 14px;
            line-height: 1.5;
            color: #475569;
            overflow-wrap: anywhere;
        }

        .job-description-text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .applicant-count {
            display: inline-flex;
            align-self: flex-start;
            margin-top: 14px;
            padding: 6px 10px;
            background: #f1f5f9;
            color: #334155;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .job-actions {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: 18px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 10px 14px;
            border: 0;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-danger:hover {
            background: #fecaca;
        }

        .notice {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 9px;
            line-height: 1.5;
        }

        .notice-success {
            background: #dcfce7;
            color: #166534;
        }

        .notice-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            padding: 42px 20px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            text-align: center;
            color: var(--muted);
        }

        .empty-state h3 {
            margin-top: 0;
            color: var(--text);
        }

        @media (max-width: 850px) {
            .sidebar {
                position: static;
                width: 100%;
            }

            .menu {
                display: flex;
                flex-wrap: wrap;
            }

            .menu li a {
                padding: 12px 14px;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .topbar {
                padding: 18px;
            }

            .topbar h1 {
                font-size: 24px;
            }

            .jobs-section {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .job-grid {
                grid-template-columns: 1fr;
            }

            .job-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <div class="logo">
        <h2>NepalJobs</h2>
    </div>

    <ul class="menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="post-job.php">Post Job</a></li>
        <li class="active"><a href="manage-jobs.php">Manage Jobs</a></li>
        <li><a href="applicants.php">Applicants</a></li>
        <li><a href="profile.php">My Profile</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</aside>

<main class="main-content">

    <header class="topbar">
        <h1>Manage Jobs</h1>

        <div class="profile">
            Welcome, <?= jobEscape($_SESSION["name"] ?? "Employer") ?>
        </div>
    </header>

    <section class="jobs-section">

        <div class="section-heading">
            <h2>My Jobs</h2>

            <span class="job-count">
                <?= count($jobs) ?> job(s)
            </span>
        </div>

        <?php if ($message !== ""): ?>
            <div class="notice <?= $message_type === "success"
                ? "notice-success"
                : "notice-error" ?>">
                <?= jobEscape($message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>

            <div class="empty-state">
                <h3>No jobs posted yet</h3>

                <p>
                    Your posted jobs will appear here after you create one.
                </p>

                <a class="btn btn-primary" href="post-job.php">
                    Post Your First Job
                </a>
            </div>

        <?php else: ?>

            <div class="job-grid">

                <?php foreach ($jobs as $job): ?>

                    <article class="job-card">

                        <h3><?= jobEscape($job["job_title"]) ?></h3>

                        <p class="company-name">
                            <?= jobEscape($job["company"]) ?>
                        </p>

                        <div class="job-details">
                            <p>
                                <strong>Location:</strong>
                                <?= jobEscape($job["location"]) ?>
                            </p>

                            <p>
                                <strong>Salary:</strong>
                                <?= jobEscape($job["salary"]) ?>
                            </p>
                        </div>

                        <div class="job-description">
                            <strong>Description</strong>

                            <div class="job-description-text"><?= jobEscape(
                                $job["description"]
                            ) ?></div>
                        </div>

                        <span class="applicant-count">
                            <?= (int)$job["applicant_count"] ?>
                            applicant(s)
                        </span>

                        <div class="job-actions">

                            <a class="btn btn-primary"
                               href="applicants.php">
                                View Applicants
                            </a>

                            <form method="POST"
                                  action="manage-jobs.php"
                                  onsubmit="return confirm('Delete this job? If it has applications, the database may prevent deletion to preserve those records.');">

                                <input type="hidden"
                                       name="job_id"
                                       value="<?= (int)$job["id"] ?>">

                                <button class="btn btn-danger"
                                        type="submit">
                                    Delete
                                </button>
                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
