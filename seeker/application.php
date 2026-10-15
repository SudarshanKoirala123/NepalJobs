
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

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "jobseeker"
) {

    header("Location: ../login.html");
    exit;

}


// ========================================
// GET JOB ID
// ========================================

if (
    !isset($_GET["job_id"]) ||
    !is_numeric($_GET["job_id"])
) {

    header("Location: jobs.php");
    exit;

}

$job_id = (int) $_GET["job_id"];


// ========================================
// GET USER ID
// ========================================

$user_id = (int) $_SESSION["user_id"];


// ========================================
// GET JOB INFORMATION
// ========================================

$stmt = $conn->prepare("
    SELECT
        id,
        company,
        job_title,
        location,
        salary,
        description
    FROM jobs
    WHERE id = ?
");

if (!$stmt) {

    die("Unable to load job information.");

}

$stmt->bind_param("i", $job_id);

$stmt->execute();

$result = $stmt->get_result();

$job = $result->fetch_assoc();

$stmt->close();


// ========================================
// CHECK JOB EXISTS
// ========================================

if (!$job) {

    echo "Job not found.";
    exit;

}


// ========================================
// CHECK IF ALREADY APPLIED
// ========================================

$check = $conn->prepare("
    SELECT id
    FROM applications
    WHERE job_id = ?
    AND applicant_id = ?
");

$check->bind_param(
    "ii",
    $job_id,
    $user_id
);

$check->execute();

$check_result = $check->get_result();

$already_applied = $check_result->num_rows > 0;

$check->close();


// ========================================
// VARIABLES
// ========================================

$message = "";
$message_type = "";


// ========================================
// PROCESS APPLICATION
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // ========================================
    // PREVENT DUPLICATE APPLICATION
    // ========================================

    if ($already_applied) {

        $message = "You have already applied for this job.";
        $message_type = "error";

    }


    else {


        // ========================================
        // GET FORM DATA
        // ========================================

        $applicant_name =
            trim($_POST["applicant_name"] ?? "");

        $applicant_email =
            trim($_POST["applicant_email"] ?? "");

        $applicant_phone =
            trim($_POST["applicant_phone"] ?? "");

        $applicant_message =
            trim($_POST["applicant_message"] ?? "");


        // ========================================
        // VALIDATE REQUIRED INFORMATION
        // ========================================

        if (
            $applicant_name === "" ||
            $applicant_email === "" ||
            $applicant_phone === ""
        ) {

            $message =
                "Please fill in all required information.";

            $message_type = "error";

        }


        // ========================================
        // VALIDATE EMAIL
        // ========================================

        elseif (
            !filter_var(
                $applicant_email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $message =
                "Please enter a valid email address.";

            $message_type = "error";

        }


        // ========================================
        // CHECK CV
        // ========================================

        elseif (
            !isset($_FILES["cv_file"]) ||
            $_FILES["cv_file"]["error"] !== UPLOAD_ERR_OK
        ) {

            $message =
                "Please upload your CV.";

            $message_type = "error";

        }


        else {

            $cv = $_FILES["cv_file"];


            // ========================================
            // CHECK FILE SIZE
            // MAXIMUM 5 MB
            // ========================================

            $max_size = 5 * 1024 * 1024;


            if ($cv["size"] > $max_size) {

                $message =
                    "CV file must be less than 5 MB.";

                $message_type = "error";

            }


            else {


                // ========================================
                // ALLOWED FILE EXTENSIONS
                // ========================================

                $allowed_extensions = [
                    "pdf",
                    "doc",
                    "docx"
                ];


                // ========================================
                // GET ORIGINAL FILE NAME
                // ========================================

                $original_name = $cv["name"];


                // ========================================
                // GET FILE EXTENSION
                // ========================================

                $extension = strtolower(
                    pathinfo(
                        $original_name,
                        PATHINFO_EXTENSION
                    )
                );


                // ========================================
                // CHECK FILE EXTENSION
                // ========================================

                if (
                    !in_array(
                        $extension,
                        $allowed_extensions,
                        true
                    )
                ) {

                    $message =
                        "Only PDF, DOC and DOCX files are allowed.";

                    $message_type = "error";

                }


                else {


                    // ========================================
                    // CV UPLOAD DIRECTORY
                    // ========================================

                    $upload_directory =
                        dirname(__DIR__) .
                        DIRECTORY_SEPARATOR .
                        "uploads" .
                        DIRECTORY_SEPARATOR .
                        "cvs" .
                        DIRECTORY_SEPARATOR;


                    // ========================================
                    // CHECK UPLOAD DIRECTORY
                    // ========================================

                    if (!is_dir($upload_directory)) {

                        $message =
                            "CV upload folder does not exist. Please create: uploads/cvs";

                        $message_type = "error";

                    }


                    elseif (!is_writable($upload_directory)) {

                        $message =
                            "CV upload folder is not writable.";

                        $message_type = "error";

                    }


                    else {


                        // ========================================
                        // CREATE UNIQUE FILE NAME
                        // ========================================

                        try {

                            $random_name =
                                bin2hex(
                                    random_bytes(8)
                                );

                        }

                        catch (Exception $e) {

                            $random_name =
                                uniqid();

                        }


                        $new_file_name =
                            "cv_" .
                            $user_id .
                            "_" .
                            time() .
                            "_" .
                            $random_name .
                            "." .
                            $extension;


                        // ========================================
                        // FINAL FILE LOCATION
                        // ========================================

                        $destination =
                            $upload_directory .
                            $new_file_name;


                        // ========================================
                        // MOVE UPLOADED CV
                        // ========================================

                        if (
                            move_uploaded_file(
                                $cv["tmp_name"],
                                $destination
                            )
                        ) {


                            // ========================================
                            // INSERT APPLICATION
                            // ========================================

                            $insert = $conn->prepare("
                                INSERT INTO applications
                                (
                                    job_id,
                                    applicant_id,
                                    applicant_name,
                                    applicant_email,
                                    applicant_phone,
                                    applicant_message,
                                    cv_file,
                                    status
                                )
                                VALUES
                                (?, ?, ?, ?, ?, ?, ?, 'Pending')
                            ");


                            // ========================================
                            // CHECK SQL PREPARE
                            // ========================================

                            if (!$insert) {

                                // Delete uploaded CV
                                // because database insert failed

                                if (
                                    file_exists(
                                        $destination
                                    )
                                ) {

                                    unlink(
                                        $destination
                                    );

                                }


                                $message =
                                    "Unable to prepare application.";

                                $message_type =
                                    "error";

                            }


                            else {


                                // ========================================
                                // BIND APPLICATION DATA
                                // ========================================

                                $insert->bind_param(
                                    "iisssss",
                                    $job_id,
                                    $user_id,
                                    $applicant_name,
                                    $applicant_email,
                                    $applicant_phone,
                                    $applicant_message,
                                    $new_file_name
                                );


                                // ========================================
                                // SAVE APPLICATION
                                // ========================================

                                if ($insert->execute()) {

                                    $message =
                                        "Application submitted successfully.";

                                    $message_type =
                                        "success";

                                    $already_applied = true;

                                }


                                else {

                                    // Delete CV if database insert fails

                                    if (
                                        file_exists(
                                            $destination
                                        )
                                    ) {

                                        unlink(
                                            $destination
                                        );

                                    }


                                    // Handle duplicate database entry

                                    if (
                                        $conn->errno === 1062
                                    ) {

                                        $message =
                                            "You have already applied for this job.";

                                    }

                                    else {

                                        $message =
                                            "Unable to submit application. Please try again.";

                                    }

                                    $message_type =
                                        "error";

                                }


                                $insert->close();

                            }

                        }


                        else {

                            $message =
                                "There was a problem uploading your CV.";

                            $message_type =
                                "error";

                        }

                    }

                }

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Apply for <?php echo htmlspecialchars($job["job_title"]); ?> - NepalJobs
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;

            color: #1f2937;

        }


        .navbar {

            background: #2563eb;

            color: white;

            padding: 16px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

        }


        .navbar h2 {

            margin: 0;

            font-size: 22px;

        }


        .navbar a {

            color: white;

            text-decoration: none;

            margin-left: 20px;

            font-weight: 500;

        }


        .navbar a:hover {

            text-decoration: underline;

        }


        .container {

            width: 90%;

            max-width: 900px;

            margin: 40px auto;

        }


        .job-card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.08);

            margin-bottom: 25px;

        }


        .job-card h1 {

            margin-top: 0;

            color: #1d4ed8;

        }


        .job-card p {

            margin: 8px 0;

            line-height: 1.6;

        }


        .application-card {

            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.08);

        }


        .application-card h2 {

            margin-top: 0;

            margin-bottom: 25px;

            color: #111827;

        }


        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;

        }


        .form-group input,

        .form-group textarea {

            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 6px;

            font-size: 15px;

        }


        .form-group input:focus,

        .form-group textarea:focus {

            outline: none;

            border-color: #2563eb;

        }


        .form-group textarea {

            min-height: 140px;

            resize: vertical;

        }


        .file-help {

            margin-top: 6px;

            font-size: 13px;

            color: #6b7280;

        }


        .submit-button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 6px;

            background: #2563eb;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

        }


        .submit-button:hover {

            background: #1d4ed8;

        }


        .back-button {

            display: inline-block;

            margin-top: 15px;

            color: #2563eb;

            text-decoration: none;

        }


        .message {

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            font-weight: 500;

        }


        .message.success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #86efac;

        }


        .message.error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fca5a5;

        }


        .already-applied {

            text-align: center;

            padding: 20px;

            background: #eff6ff;

            border: 1px solid #bfdbfe;

            border-radius: 8px;

            color: #1e40af;

        }


        .required {

            color: red;

        }


        @media (max-width: 600px) {

            .navbar {

                padding: 15px;

            }


            .navbar a {

                margin-left: 10px;

                font-size: 14px;

            }


            .container {

                width: 94%;

                margin: 25px auto;

            }


            .application-card,

            .job-card {

                padding: 20px;

            }

        }

    </style>

</head>


<body>


<!-- ========================================
     NAVBAR
======================================== -->

<nav class="navbar">

    <h2>NepalJobs</h2>

    <div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="jobs.php">
            Jobs
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- ========================================
     MAIN CONTAINER
======================================== -->

<div class="container">


    <!-- ========================================
         JOB INFORMATION
    ======================================== -->

    <div class="job-card">

        <h1>
            <?php
            echo htmlspecialchars(
                $job["job_title"]
            );
            ?>
        </h1>


        <p>
            <strong>Company:</strong>

            <?php
            echo htmlspecialchars(
                $job["company"]
            );
            ?>
        </p>


        <p>
            <strong>Location:</strong>

            <?php
            echo htmlspecialchars(
                $job["location"]
            );
            ?>
        </p>


        <p>
            <strong>Salary:</strong>

            <?php
            echo htmlspecialchars(
                $job["salary"]
            );
            ?>
        </p>


        <p>
            <strong>Job Description:</strong>
        </p>


        <p>
            <?php
            echo nl2br(
                htmlspecialchars(
                    $job["description"]
                )
            );
            ?>
        </p>

    </div>



    <!-- ========================================
         APPLICATION FORM
    ======================================== -->

    <div class="application-card">

        <h2>
            Apply for this Job
        </h2>


        <!-- ========================================
             MESSAGE
        ======================================== -->

        <?php if ($message !== ""): ?>

            <div
                class="message <?php echo $message_type; ?>"
            >

                <?php
                echo htmlspecialchars(
                    $message
                );
                ?>

            </div>

        <?php endif; ?>



        <!-- ========================================
             ALREADY APPLIED
        ======================================== -->

        <?php if ($already_applied): ?>

            <div class="already-applied">

                <strong>
                    You have already applied for this job.
                </strong>

                <br><br>

                You cannot submit another application
                for the same job.

                <br><br>

                <a
                    href="jobs.php"
                    style="
                        color:#2563eb;
                        font-weight:bold;
                        text-decoration:none;
                    "
                >
                    Browse More Jobs
                </a>

            </div>


        <?php else: ?>


            <!-- ========================================
                 APPLICATION FORM
            ======================================== -->

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="applicant_name">

                        Full Name
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="applicant_name"
                        name="applicant_name"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["applicant_name"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="applicant_email">

                        Email
                        <span class="required">*</span>

                    </label>


                    <input
                        type="email"
                        id="applicant_email"
                        name="applicant_email"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["applicant_email"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label for="applicant_phone">

                        Phone Number
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="applicant_phone"
                        name="applicant_phone"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["applicant_phone"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- COVER MESSAGE -->

                <div class="form-group">

                    <label for="applicant_message">

                        Cover Message

                    </label>


                    <textarea
                        id="applicant_message"
                        name="applicant_message"
                        placeholder="Write a short message to the employer..."
                    ><?php
                        echo htmlspecialchars(
                            $_POST["applicant_message"] ?? ""
                        );
                    ?></textarea>

                </div>



                <!-- CV -->

                <div class="form-group">

                    <label for="cv_file">

                        Upload CV
                        <span class="required">*</span>

                    </label>


                    <input
                        type="file"
                        id="cv_file"
                        name="cv_file"
                        accept=".pdf,.doc,.docx"
                        required
                    >


                    <div class="file-help">

                        Required.
                        PDF, DOC or DOCX only.
                        Maximum file size: 5 MB.

                    </div>

                </div>



                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-button"
                >
                    Submit Application
                </button>


            </form>


        <?php endif; ?>


        <a
            href="jobs.php"
            class="back-button"
        >
            ← Back to Jobs
        </a>


    </div>


</div>


</body>

</html>

