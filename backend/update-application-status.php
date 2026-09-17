<?php

session_start();

require_once "db.php";

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


// ========================================
// ONLY POST REQUESTS
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../employer/applicants.php");
    exit;
}


// ========================================
// GET DATA
// ========================================

$application_id = intval($_POST["application_id"] ?? 0);
$status = trim($_POST["status"] ?? "");


// ========================================
// VALIDATE
// ========================================

$allowed_statuses = [
    "Pending",
    "Shortlisted",
    "Rejected",
    "Accepted"
];

if ($application_id <= 0) {
    die("Invalid application.");
}

if (!in_array($status, $allowed_statuses, true)) {
    die("Invalid application status.");
}


// ========================================
// CHECK THAT APPLICATION BELONGS
// TO THIS EMPLOYER
// ========================================

$check = $conn->prepare("
    SELECT a.id
    FROM applications a
    INNER JOIN jobs j
        ON a.job_id = j.id
    WHERE a.id = ?
    AND j.employer_id = ?
");

$check->bind_param(
    "ii",
    $application_id,
    $_SESSION["user_id"]
);

$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {

    $check->close();
    $conn->close();

    die("You are not authorized to update this application.");

}

$check->close();


// ========================================
// UPDATE STATUS
// ========================================

$update = $conn->prepare("
    UPDATE applications
    SET status = ?
    WHERE id = ?
");

$update->bind_param(
    "si",
    $status,
    $application_id
);


if ($update->execute()) {

    $update->close();
    $conn->close();

    header("Location: ../employer/applicants.php");
    exit;

} else {

    $error = $update->error;

    $update->close();
    $conn->close();

    die("Failed to update application: " . $error);

}

?>
