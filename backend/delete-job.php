<?php

session_start();

require_once "db.php";

header("Content-Type: application/json");

// Check login
if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);

    exit;
}

// Check employer role
if ($_SESSION["role"] !== "employer") {

    echo json_encode([
        "success" => false,
        "message" => "Employer access required."
    ]);

    exit;
}

// Get job ID
$jobId = intval($_POST["job_id"] ?? 0);

if ($jobId <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid job ID."
    ]);

    exit;
}

$employerId = $_SESSION["user_id"];

// Delete only if this job belongs to this employer
$stmt = $conn->prepare(
    "DELETE FROM jobs
     WHERE id = ?
     AND employer_id = ?"
);

$stmt->bind_param(
    "ii",
    $jobId,
    $employerId
);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        echo json_encode([
            "success" => true,
            "message" => "Job deleted successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Job not found or access denied."
        ]);

    }

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to delete job."
    ]);

}

$stmt->close();
$conn->close();

?>