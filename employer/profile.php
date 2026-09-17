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
// UPDATE PROFILE
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "" || $email === "") {

        $message = "Name and email are required.";

    } else {

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
        ");

        $check->bind_param(
            "si",
            $email,
            $_SESSION["user_id"]
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $message = "This email is already being used.";

        } else {

            $update = $conn->prepare("
                UPDATE users
                SET name = ?, email = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "ssi",
                $name,
                $email,
                $_SESSION["user_id"]
            );


            if ($update->execute()) {

                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;

                $message = "Profile updated successfully.";

            } else {

                $message = "Failed to update profile.";

            }

            $update->close();

        }

        $check->close();

    }
}


// ========================================
// GET USER
// ========================================

$stmt = $conn->prepare("
    SELECT name, email, role
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

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Employer Profile - NepalJobs</title>

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

        <li class="active">
            <a href="profile.php">My Profile</a>
        </li>

        <li>
            <a href="settings.php">Settings</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <h1>My Profile</h1>

        <div class="profile">
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </div>

    </div>


    <section class="table-section">

        <h2>Profile Information</h2>


        <?php if ($message !== ""): ?>

            <p style="
                padding:15px;
                margin-bottom:20px;
                background:#eee;
            ">

                <?php echo htmlspecialchars($message); ?>

            </p>

        <?php endif; ?>


        <form method="POST" action="profile.php">


            <div style="margin-bottom:15px;">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($user["name"]); ?>"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($user["email"]); ?>"
                    required
                >

            </div>


            <div style="margin-bottom:15px;">

                <label>Role</label>

                <input
                    type="text"
                    value="Employer"
                    readonly
                >

            </div>


            <button type="submit">
                Update Profile
            </button>


        </form>

    </section>

</div>

</body>
</html>