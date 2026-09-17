<?php

session_start();

require_once "../backend/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

if ($_SESSION["role"] !== "employer") {
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


    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $message = "Please fill in all fields.";

    } elseif ($newPassword !== $confirmPassword) {

        $message = "New passwords do not match.";

    } elseif (strlen($newPassword) < 6) {

        $message = "New password must be at least 6 characters.";

    } else {

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

        } elseif (!password_verify(
            $currentPassword,
            $user["password"]
        )) {

            $message = "Current password is incorrect.";

        } else {

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


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

    <title>Employer Settings - NepalJobs</title>

    <link rel="stylesheet" href="employer.css">

</head>

<body>

<div class="sidebar">

    <div class="logo">
        <h2>NepalJobs</h2>
    </div>

    <ul class="menu">

        <li>
            <a href="dashboard.php">Dashboard</a>
        </li>

        <li>
            <a href="post-job.php">Post Job</a>
        </li>

        <li>
            <a href="manage-jobs.php">Manage Jobs</a>
        </li>

        <li>
            <a href="applicants.php">Applicants</a>
        </li>

        <li>
            <a href="profile.php">My Profile</a>
        </li>

        <li class="active">
            <a href="settings.php">Settings</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <h1>Settings</h1>

        <div class="profile">
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </div>

    </div>


    <section class="table-section">

        <h2>Change Password</h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                background:#eee;
            ">

                <?php echo htmlspecialchars($message); ?>

            </p>

        <?php endif; ?>


        <form method="POST" action="settings.php">


            <div style="margin-bottom:15px;">

                <label>Current Password</label>

                <input
                    type="password"
                    name="current_password"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>New Password</label>

                <input
                    type="password"
                    name="new_password"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Confirm New Password</label>

                <input
                    type="password"
                    name="confirm_password"
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