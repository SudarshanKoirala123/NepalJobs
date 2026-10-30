
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
$message_type = "success";

if (empty($_SESSION["applicants_csrf"])) {
    $_SESSION["applicants_csrf"] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION["applicants_csrf"];

// ========================================
// ACCEPT / REJECT APPLICATION
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $posted_token = $_POST["csrf_token"] ?? "";
    $application_id = filter_input(
        INPUT_POST,
        "application_id",
        FILTER_VALIDATE_INT
    );
    $action = $_POST["action"] ?? "";

    if (
        !is_string($posted_token) ||
        !hash_equals($csrf_token, $posted_token)
    ) {
        $message = "Security verification failed. Please refresh the page and try again.";
        $message_type = "error";

    } elseif (!$application_id || $application_id < 1) {
        $message = "Invalid application selected.";
        $message_type = "error";

    } elseif (!in_array($action, ["accept", "reject"], true)) {
        $message = "Invalid application action.";
        $message_type = "error";

    } else {

        $new_status = ($action === "accept") ? "Accepted" : "Rejected";

        // Update only applications belonging to this employer's jobs.
        $update = $conn->prepare("
            UPDATE applications AS a
            INNER JOIN jobs AS j ON j.id = a.job_id
            SET a.status = ?
            WHERE a.id = ?
              AND j.employer_id = ?
        ");

        if (!$update) {
            $message = "Unable to prepare the status update.";
            $message_type = "error";

        } else {

            $update->bind_param(
                "sii",
                $new_status,
                $application_id,
                $employer_id
            );

            try {
                if ($update->execute()) {

                    if ($update->affected_rows > 0) {
                        $message = "Application status updated to " . $new_status . ".";
                        $message_type = "success";
                    } else {
                        // Check whether this application belongs to this employer.
                        $verify = $conn->prepare("
                            SELECT a.status
                            FROM applications AS a
                            INNER JOIN jobs AS j ON j.id = a.job_id
                            WHERE a.id = ?
                              AND j.employer_id = ?
                        ");

                        $verify->bind_param(
                            "ii",
                            $application_id,
                            $employer_id
                        );

                        $verify->execute();
                        $verify_result = $verify->get_result();

                        if ($verify_result->num_rows > 0) {
                            $current = $verify_result->fetch_assoc();

                            if ($current["status"] === $new_status) {
                                $message = "This application is already marked " . $new_status . ".";
                                $message_type = "success";
                            } else {
                                $message = "The application status was not changed.";
                                $message_type = "error";
                            }
                        } else {
                            $message = "Application not found or you do not have permission to update it.";
                            $message_type = "error";
                        }

                        $verify->close();
                    }

                } else {
                    $message = "Unable to update the application status.";
                    $message_type = "error";
                }

            } catch (mysqli_sql_exception $e) {
                $message = "A database error occurred while updating the application.";
                $message_type = "error";
            }

            $update->close();
        }
    }
}

// ========================================
// GET EMPLOYER'S JOB COUNT
// ========================================

$job_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM jobs
    WHERE employer_id = ?
");

$job_stmt->bind_param("i", $employer_id);
$job_stmt->execute();

$job_result = $job_stmt->get_result();
$job_data = $job_result->fetch_assoc();

$total_jobs = (int) ($job_data["total"] ?? 0);

$job_stmt->close();

// ========================================
// GET APPLICATIONS
// ========================================

$sql = "
    SELECT
        a.id AS application_id,
        a.job_id,
        a.applicant_id,
        a.applicant_name,
        a.applicant_email,
        a.applicant_phone,
        a.applicant_message,
        a.cv_file,
        a.status,
        a.applied_at,
        j.job_title,
        j.company,
        j.location
    FROM applications AS a
    INNER JOIN jobs AS j ON j.id = a.job_id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC, a.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();

$result = $stmt->get_result();
$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$stmt->close();

function applicantEscape($value) {
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

    <title>Job Applicants - NepalJobs</title>

    <link rel="stylesheet" href="employer.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6fb;
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
        }

        .applicants-container {
            max-width: 1350px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 25px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .05);
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .page-description {
            margin: 0;
            color: #64748b;
            line-height: 1.5;
        }

        .back-link {
            display: inline-block;
            padding: 11px 15px;
            border-radius: 8px;
            background: #eaf1ff;
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
        }

        .back-link:hover {
            background: #dbeafe;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 22px 0;
        }

        .summary-card {
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
        }

        .summary-card span {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 13px;
        }

        .summary-card strong {
            font-size: 27px;
        }

        .notice {
            padding: 15px 18px;
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

        .applications-heading {
            margin: 28px 0 18px;
            font-size: 22px;
        }

        .application-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .application-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 22px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, .04);
        }

        .applicant-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .applicant-name {
            margin: 0 0 7px;
            font-size: 19px;
            overflow-wrap: anywhere;
        }

        .application-number {
            color: #64748b;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            flex-shrink: 0;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-other {
            background: #e2e8f0;
            color: #334155;
        }

        .job-panel {
            padding: 14px;
            margin-bottom: 17px;
            background: #f8fafc;
            border-radius: 9px;
        }

        .job-panel h3 {
            margin: 0 0 7px;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .job-panel p {
            margin: 5px 0;
            color: #64748b;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .detail-block {
            margin-bottom: 16px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .detail-label {
            display: block;
            margin-bottom: 5px;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .detail-value {
            font-size: 14px;
        }

        .cover-message {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .cv-link {
            display: inline-block;
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .cv-link:hover {
            text-decoration: underline;
        }

        .application-date {
            margin-top: auto;
            padding-top: 13px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }

        .action-row {
            display: flex;
            gap: 10px;
            margin-top: 17px;
        }

        .action-form {
            flex: 1;
            margin: 0;
        }

        .action-button {
            width: 100%;
            padding: 11px 12px;
            border: 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s;
        }

        .accept-button {
            background: #16a34a;
            color: white;
        }

        .accept-button:hover {
            background: #15803d;
        }

        .reject-button {
            background: #fee2e2;
            color: #b91c1c;
        }

        .reject-button:hover {
            background: #fecaca;
        }

        .action-button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .empty-state {
            padding: 45px 20px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: white;
            text-align: center;
        }

        .empty-state h2 {
            margin-top: 0;
        }

        .empty-state p {
            color: #64748b;
        }

        @media (max-width: 700px) {
            .applicants-container {
                padding: 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .summary-card {
                padding: 15px;
            }

            .summary-card strong {
                font-size: 23px;
            }

            .application-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<main class="applicants-container">

    <header class="page-header">
        <div>
            <h1>Job Applicants</h1>
            <p class="page-description">
                Review CVs and manage applications for your posted jobs.
            </p>
        </div>

        <a class="back-link" href="dashboard.php">
            &larr; Back to Dashboard
        </a>
    </header>

    <?php
    $pending_count = 0;
    $accepted_count = 0;
    $rejected_count = 0;

    foreach ($applications as $application) {
        $status = strtolower(trim((string)$application["status"]));

        if ($status === "pending") {
            $pending_count++;
        } elseif ($status === "accepted") {
            $accepted_count++;
        } elseif ($status === "rejected") {
            $rejected_count++;
        }
    }
    ?>

    <section class="summary-grid">
        <div class="summary-card">
            <span>Your posted jobs</span>
            <strong><?= $total_jobs ?></strong>
        </div>

        <div class="summary-card">
            <span>Total applicants</span>
            <strong><?= count($applications) ?></strong>
        </div>

        <div class="summary-card">
            <span>Awaiting review</span>
            <strong><?= $pending_count ?></strong>
        </div>
    </section>

    <?php if ($message !== ""): ?>
        <div class="notice <?= $message_type === "success"
            ? "notice-success"
            : "notice-error" ?>">
            <?= applicantEscape($message) ?>
        </div>
    <?php endif; ?>

    <h2 class="applications-heading">Applications received</h2>

    <?php if (empty($applications)): ?>

        <section class="empty-state">
            <?php if ($total_jobs === 0): ?>
                <h2>No jobs posted yet</h2>
                <p>
                    Post a job first. Applications for your jobs will appear here.
                </p>
                <a class="back-link" href="post-job.php">Post a Job</a>
            <?php else: ?>
                <h2>No applicants yet</h2>
                <p>
                    Your jobs are listed, but nobody has applied to them yet.
                </p>
                <a class="back-link" href="manage-jobs.php">Manage Jobs</a>
            <?php endif; ?>
        </section>

    <?php else: ?>

        <section class="application-grid">

            <?php foreach ($applications as $application): ?>

                <?php
                $status = trim((string)($application["status"] ?? "Pending"));
                $status_lower = strtolower($status);

                if ($status_lower === "accepted") {
                    $status_class = "status-accepted";
                } elseif ($status_lower === "rejected") {
                    $status_class = "status-rejected";
                } elseif ($status_lower === "pending") {
                    $status_class = "status-pending";
                } else {
                    $status_class = "status-other";
                }

                $cv_name = basename(
                    (string)($application["cv_file"] ?? "")
                );

                $cv_path = __DIR__
                    . "/../uploads/cvs/"
                    . $cv_name;

                $cv_url = "../uploads/cvs/"
                    . rawurlencode($cv_name);

                $has_cv = (
                    $cv_name !== "" &&
                    is_file($cv_path)
                );
                ?>

                <article class="application-card">

                    <div class="applicant-top">
                        <div>
                            <h2 class="applicant-name">
                                <?= applicantEscape(
                                    $application["applicant_name"]
                                ) ?>
                            </h2>

                            <span class="application-number">
                                Application #<?= (int)$application["application_id"] ?>
                            </span>
                        </div>

                        <span class="status-badge <?= $status_class ?>">
                            <?= applicantEscape($status) ?>
                        </span>
                    </div>

                    <div class="job-panel">
                        <h3>
                            <?= applicantEscape($application["job_title"]) ?>
                        </h3>

                        <p>
                            <?= applicantEscape($application["company"]) ?>
                        </p>

                        <p>
                            <?= applicantEscape($application["location"]) ?>
                        </p>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Email address</span>
                        <div class="detail-value">
                            <?= applicantEscape($application["applicant_email"]) ?>
                        </div>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Phone number</span>
                        <div class="detail-value">
                            <?= applicantEscape($application["applicant_phone"]) ?>
                        </div>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Cover message</span>

                        <div class="detail-value cover-message"><?=
                            applicantEscape(
                                $application["applicant_message"]
                            )
                        ?></div>
                    </div>

                    <div class="detail-block">
                        <span class="detail-label">Resume / CV</span>

                        <?php if ($has_cv): ?>
                            <a
                                class="cv-link"
                                href="<?= applicantEscape($cv_url) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View or download CV &rarr;
                            </a>
                        <?php elseif ($cv_name !== ""): ?>
                            <span class="detail-value">
                                CV file not found on the server.
                            </span>
                        <?php else: ?>
                            <span class="detail-value">
                                No CV uploaded.
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="application-date">
                        Applied:
                        <?= applicantEscape($application["applied_at"]) ?>
                    </div>

                    <div class="action-row">

                        <form
                            class="action-form"
                            method="POST"
                            action="applicants.php"
                            onsubmit="return confirm('Accept this applicant?');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= applicantEscape($csrf_token) ?>"
                            >

                            <input
                                type="hidden"
                                name="application_id"
                                value="<?= (int)$application["application_id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="accept"
                            >

                            <button
                                class="action-button accept-button"
                                type="submit"
                                <?= $status_lower === "accepted" ? "disabled" : "" ?>
                            >
                                <?= $status_lower === "accepted"
                                    ? "Accepted"
                                    : "Accept" ?>
                            </button>
                        </form>

                        <form
                            class="action-form"
                            method="POST"
                            action="applicants.php"
                            onsubmit="return confirm('Reject this applicant?');"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= applicantEscape($csrf_token) ?>"
                            >

                            <input
                                type="hidden"
                                name="application_id"
                                value="<?= (int)$application["application_id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="reject"
                            >

                            <button
                                class="action-button reject-button"
                                type="submit"
                                <?= $status_lower === "rejected" ? "disabled" : "" ?>
                            >
                                <?= $status_lower === "rejected"
                                    ? "Rejected"
                                    : "Reject" ?>
                            </button>
                        </form>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

</body>
</html>
