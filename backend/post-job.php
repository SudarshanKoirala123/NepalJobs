<?php

session_start();

require_once "db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

// Only employers can post jobs
if ($_SESSION["role"] !== "employer") {
    die("Only employers can post jobs.");
}

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

// Get form data
$company = trim($_POST["company"] ?? "");
$title = trim($_POST["title"] ?? "");
$location = trim($_POST["location"] ?? "");
$salary = trim($_POST["salary"] ?? "");
$description = trim($_POST["description"] ?? "");

// Validate required fields
if ($company === "" || $title === "" || $location === "" || $description === "") {
    die("Please fill in all required fields.");
}

// Get logged-in employer ID
$employerId = $_SESSION["user_id"];

// Check for duplicate job
$check = $conn->prepare(
    "SELECT id FROM jobs
     WHERE employer_id = ?
     AND company = ?
     AND job_title = ?
     AND location = ?"
);

$check->bind_param(
    "isss",
    $employerId,
    $company,
    $title,
    $location
);

$check->execute();
$check->store_result();

if ($check->num_rows > 0) {

    $check->close();
    $conn->close();

    die("This job has already been posted.");

}

$check->close();

// Insert job
$stmt = $conn->prepare(
    "INSERT INTO jobs
    (employer_id, company, job_title, location, salary, description)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isssss",
    $employerId,
    $company,
    $title,
    $location,
    $salary,
    $description
);

if ($stmt->execute()) {

    echo "Job posted successfully!";

} else {

    echo "Job posting failed: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>