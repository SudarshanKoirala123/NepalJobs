<?php

session_start();

require_once "db.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

// Get login data
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Validate
if ($email === "" || $password === "") {
    die("Please enter your email and password.");
}

// Find user
$stmt = $conn->prepare(
    "SELECT id, name, email, password, role
     FROM users
     WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();
    $conn->close();

    die("Invalid email or password.");

}

// Get user
$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user["password"])) {

    $stmt->close();
    $conn->close();

    die("Invalid email or password.");

}

// Store user in PHP session
$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

// Close connection
$stmt->close();
$conn->close();


// Redirect based on role
if ($user["role"] === "jobseeker") {

    header("Location: ../seeker/dashboard.html");
    exit;

} elseif ($user["role"] === "employer") {

    header("Location: ../employer/dashboard.html");
    exit;

} elseif ($user["role"] === "admin") {

    header("Location: ../admin/admin.html");
    exit;

} else {

    die("Your account role is not recognized.");

}

?>