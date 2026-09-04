<?php

// NepalJobs - MySQL Database Connection

$host = "localhost";
$username = "root";
$password = "";
$database = "nepaljobs";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Use UTF-8
$conn->set_charset("utf8mb4");

?>