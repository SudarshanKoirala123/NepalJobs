<?php

require_once "db.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

// Get form data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "jobseeker";

// Validate required fields
if ($name === "" || $email === "" || $password === "") {
    die("Please fill in all required fields.");
}

// Validate role
if ($role !== "employer" && $role !== "jobseeker") {
    die("Invalid role.");
}

// Check if email already exists
$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {

    $check->close();

    die("This email is already registered.");

}

$check->close();

// Securely hash password
$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Insert new user
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $hashedPassword,
    $role
);

if ($stmt->execute()) {

    echo "Registration successful!";

} else {

    echo "Registration failed: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>