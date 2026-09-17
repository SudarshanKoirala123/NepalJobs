<?php

session_start();

require_once "../backend/db.php";

// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}


// ========================================
// ONLY JOB SEEKERS
// ========================================

if ($_SESSION["role"] !== "jobseeker") {
    header("Location: ../login.html");
    exit;
}


$message = "";


// ========================================
// CHANGE PASSWORD
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";


    // Check empty fields

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $message = "Please fill in all password fields.";

    }

    // Check new passwords match

    elseif ($newPassword !== $confirmPassword) {

        $message = "New passwords do not match.";

    }

    // Check password length

    elseif (strlen($newPassword) < 6) {

        $message = "New password must be at least 6 characters.";

    }

    else {

        // Get current password

        $stmt = $conn->prepare("
            SELECT password
            FROM users
            WHERE id = ?
        ");

        $stmt->bind_param(
            "i",
            $_SESSION["user_id"]
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        if (!$user) {

            $message = "User not found.";

        }

        // Verify old password

        elseif (!password_verify(
            $currentPassword,
            $user["password"]
        )) {

            $message = "Current password is incorrect.";

        }

        else {

            // Hash new password

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


            // Update password

            $update = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "si",
                $hashedPassword,
                $_SESSION["user_id"]
            );


            if ($update->execute()) {

                $message = "Password changed successfully.";

            } else {

                $message = "Failed to change password.";

            }


            $update->close();

        }

    }

}


$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Settings - NepalJobs</title>

    <link rel="stylesheet" href="seeker.css">

</head>

<body>


<!-- ========================================
     SIDEBAR
======================================== -->

<div class="sidebar">

    <div class="logo">

        <h2>NepalJobs</h2>

    </div>


    <ul class="menu">


        <li>

            <a href="dashboard.php">
                <span>Dashboard</span>
            </a>

        </li>


        <li>

            <a href="jobs.php">
                <span>Find Jobs</span>
            </a>

        </li>


        <li>

            <a href="application.php">
                <span>My Applications</span>
            </a>

        </li>


        <li>

            <a href="profile.php">
                <span>My Profile</span>
            </a>

        </li>


        <li class="active">

            <a href="settings.php">
                <span>Settings</span>
            </a>

        </li>


        <li>

            <a href="logout.php">
                <span>Logout</span>
            </a>

        </li>


    </ul>

</div>


<!-- ========================================
     MAIN CONTENT
======================================== -->

<div class="main-content">


    <div class="topbar">

        <h1>
            Settings
        </h1>


        <div class="profile">

            <span>

                <?php
                echo htmlspecialchars($_SESSION["name"]);
                ?>

            </span>

        </div>

    </div>


    <!-- ========================================
         CHANGE PASSWORD
    ======================================== -->

    <section class="table-section">

        <h2>
            Change Password
        </h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                border-radius:6px;
                background:#eee;
            ">

                <?php
                echo htmlspecialchars($message);
                ?>

            </p>

        <?php endif; ?>


        <form method="POST" action="settings.php">


            <div style="margin-bottom:15px;">

                <label>
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    placeholder="Enter current password"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    placeholder="Enter new password"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >

            </div>


            <button type="submit">

                Change Password

            </button>


        </form>


    </section>


</div>


</body>

</html>