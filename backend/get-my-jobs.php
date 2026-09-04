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

$employerId = $_SESSION["user_id"];

// Get jobs posted by this employer
$stmt = $conn->prepare(
    "SELECT id, company, job_title, location, salary, description, created_at
     FROM jobs
     WHERE employer_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $employerId);

$stmt->execute();

$result = $stmt->get_result();

$jobs = [];

while ($row = $result->fetch_assoc()) {

    $jobs[] = $row;

}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "jobs" => $jobs
]);

?>