
<?php
session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.html");
    exit();
}

require_once __DIR__ . "/../backend/db.php";

// ========================================
// SECURITY AND HELPERS
// ========================================

if (empty($_SESSION["users_csrf_token"])) {
    $_SESSION["users_csrf_token"] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION["users_csrf_token"];
$message = "";
$message_type = "success";

function userEscape($value): string
{
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// ========================================
// ACCEPT OR REJECT USER
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $posted_token = $_POST["csrf_token"] ?? "";
    $user_id = filter_input(
        INPUT_POST,
        "user_id",
        FILTER_VALIDATE_INT
    );
    $action = $_POST["action"] ?? "";

    if (
        !is_string($posted_token) ||
        !hash_equals($csrf_token, $posted_token)
    ) {
        $message = "Security verification failed. Refresh the page and try again.";
        $message_type = "error";

    } elseif (!$user_id || $user_id < 1) {
        $message = "Invalid user selected.";
        $message_type = "error";

    } elseif (!in_array($action, ["accept", "reject"], true)) {
        $message = "Invalid action selected.";
        $message_type = "error";

    } else {

        $new_status = $action === "accept"
            ? "Accepted"
            : "Rejected";

        $update = $conn->prepare("
            UPDATE users
            SET account_status = ?
            WHERE id = ?
        ");

        if (!$update) {
            $message = "Unable to prepare the database update.";
            $message_type = "error";

        } else {

            $update->bind_param(
                "si",
                $new_status,
                $user_id
            );

            try {

                if ($update->execute()) {

                    if ($update->affected_rows > 0) {
                        $message = "User account status updated to " . $new_status . ".";
                        $message_type = "success";

                    } else {

                        // The account may already have the requested status.
                        $check = $conn->prepare("
                            SELECT id, account_status
                            FROM users
                            WHERE id = ?
                        ");

                        $check->bind_param("i", $user_id);
                        $check->execute();

                        $check_result = $check->get_result();
                        $existing_user = $check_result->fetch_assoc();

                        if (!$existing_user) {
                            $message = "User not found.";
                            $message_type = "error";

                        } elseif ($existing_user["account_status"] === $new_status) {
                            $message = "This account is already " . $new_status . ".";
                            $message_type = "success";

                        } else {
                            $message = "No changes were made.";
                            $message_type = "error";
                        }

                        $check->close();
                    }

                } else {
                    $message = "Failed to update the user account.";
                    $message_type = "error";
                }

            } catch (mysqli_sql_exception $e) {
                $message = "A database error occurred while updating the account.";
                $message_type = "error";
            }

            $update->close();
        }
    }
}

// ========================================
// FETCH USERS
// ========================================

$result = $conn->query("
    SELECT
        id,
        name,
        email,
        role,
        account_status,
        last_activity
    FROM users
    ORDER BY id DESC
");

if (!$result) {
    die(
        "Unable to load users. Please confirm that the account_status "
        . "and last_activity columns have been added to the users table."
    );
}

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$total_users = count($users);
$pending_users = 0;
$accepted_users = 0;
$rejected_users = 0;
$online_users = 0;

$now = time();

foreach ($users as $user) {

    $status = $user["account_status"] ?? "Pending";

    if ($status === "Pending") {
        $pending_users++;
    } elseif ($status === "Accepted") {
        $accepted_users++;
    } elseif ($status === "Rejected") {
        $rejected_users++;
    }

    if (!empty($user["last_activity"])) {
        $activity_time = strtotime($user["last_activity"]);

        if (
            $activity_time !== false &&
            $activity_time >= ($now - 300) &&
            $activity_time <= ($now + 60)
        ) {
            $online_users++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - NepalJobs</title>

    <link rel="stylesheet" href="admin.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6fb;
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
        }

        .users-main {
            min-width: 0;
            padding: 28px;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .users-header h1 {
            margin: 0 0 8px;
            font-size: 29px;
        }

        .users-header p {
            margin: 0;
            color: #64748b;
        }

        .users-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .users-stat {
            padding: 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
        }

        .users-stat span {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 9px;
        }

        .users-stat strong {
            font-size: 27px;
        }

        .users-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
        }

        .users-panel h2 {
            margin: 0 0 6px;
            font-size: 21px;
        }

        .users-panel-description {
            margin: 0 0 20px;
            color: #64748b;
            font-size: 14px;
        }

        .users-alert {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 9px;
            line-height: 1.5;
        }

        .users-alert-success {
            color: #166534;
            background: #dcfce7;
        }

        .users-alert-error {
            color: #991b1b;
            background: #fee2e2;
        }

        .users-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
        }

        .users-table th {
            padding: 14px 13px;
            background: #172554;
            color: #fff;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .users-table td {
            padding: 15px 13px;
            border-bottom: 1px solid #e8edf4;
            font-size: 14px;
            vertical-align: middle;
        }

        .users-table tbody tr:hover {
            background: #f8fafc;
        }

        .user-name {
            font-weight: 700;
            color: #172033;
        }

        .user-email {
            color: #475569;
            overflow-wrap: anywhere;
        }

        .user-role {
            display: inline-block;
            padding: 6px 9px;
            border-radius: 7px;
            background: #eaf1ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }

        .account-badge,
        .activity-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .account-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .account-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .account-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .activity-online {
            background: #dcfce7;
            color: #166534;
        }

        .activity-offline {
            background: #e2e8f0;
            color: #475569;
        }

        .activity-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .user-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .user-action-form {
            margin: 0;
        }

        .user-action-btn {
            border: 0;
            padding: 9px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .user-accept {
            background: #15803d;
            color: #fff;
        }

        .user-reject {
            background: #fee2e2;
            color: #b91c1c;
        }

        .user-action-btn:hover {
            filter: brightness(.94);
        }

        .user-action-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .user-last-seen {
            color: #64748b;
            font-size: 12px;
            margin-top: 5px;
            white-space: nowrap;
        }

        .users-empty {
            padding: 30px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 1100px) {
            .users-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .users-main {
                padding: 20px;
            }
        }

        @media (max-width: 600px) {
            .users-stats {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .users-stat {
                padding: 15px;
            }

            .users-stat strong {
                font-size: 23px;
            }

            .users-panel {
                padding: 14px;
            }

            .users-header h1 {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

<div class="admin-container">

    <aside class="admin-sidebar">

        <h2>NepalJobs</h2>
        <p class="admin-title">Admin Panel</p>

        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li class="active"><a href="users.php">Manage Users</a></li>
            <li><a href="employers.php">Manage Employers</a></li>
            <li><a href="jobseekers.php">Manage Job Seekers</a></li>
            <li><a href="manage-jobs.php">Manage Jobs</a></li>
            <li><a href="applicants.php">Applications</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../index.html">Back to Website</a></li>
            <li><a href="../backend/admin-logout.php">Logout</a></li>
        </ul>

    </aside>

    <main class="admin-main users-main">

        <header class="users-header">
            <div>
                <h1>Manage Users</h1>
                <p>Review registrations and monitor recent user activity.</p>
            </div>
        </header>

        <?php if ($message !== ""): ?>
            <div class="users-alert <?= $message_type === "success"
                ? "users-alert-success"
                : "users-alert-error" ?>">
                <?= userEscape($message) ?>
            </div>
        <?php endif; ?>

        <section class="users-stats">

            <div class="users-stat">
                <span>Total users</span>
                <strong><?= $total_users ?></strong>
            </div>

            <div class="users-stat">
                <span>Accepted accounts</span>
                <strong><?= $accepted_users ?></strong>
            </div>

            <div class="users-stat">
                <span>Pending approval</span>
                <strong><?= $pending_users ?></strong>
            </div>

            <div class="users-stat">
                <span>Recently active</span>
                <strong><?= $online_users ?></strong>
            </div>

        </section>

        <section class="users-panel">

            <h2>Registered Users</h2>
            <p class="users-panel-description">
                Accept or reject accounts and review recent activity.
            </p>

            <div class="users-table-wrap">

                <table class="users-table">

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Account Approval</th>
                        <th>Activity</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($users)): ?>

                        <tr>
                            <td colspan="6" class="users-empty">
                                No registered users found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <?php
                            $status = $user["account_status"] ?? "Pending";

                            $account_class = match ($status) {
                                "Accepted" => "account-accepted",
                                "Rejected" => "account-rejected",
                                default => "account-pending"
                            };

                            $activity_time = !empty($user["last_activity"])
                                ? strtotime($user["last_activity"])
                                : false;

                            $is_online = (
                                $activity_time !== false &&
                                $activity_time >= (time() - 300) &&
                                $activity_time <= (time() + 60)
                            );
                            ?>

                            <tr>

                                <td><?= (int)$user["id"] ?></td>

                                <td>
                                    <div class="user-name">
                                        <?= userEscape($user["name"]) ?>
                                    </div>
                                    <div class="user-email">
                                        <?= userEscape($user["email"]) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="user-role">
                                        <?= userEscape($user["role"]) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="account-badge <?= $account_class ?>">
                                        <?= userEscape($status) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($is_online): ?>

                                        <span class="activity-badge activity-online">
                                            <span class="activity-dot"></span>
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="activity-badge activity-offline">
                                            <span class="activity-dot"></span>
                                            Offline
                                        </span>

                                    <?php endif; ?>

                                    <div class="user-last-seen">
                                        <?php if ($activity_time !== false): ?>
                                            Last activity:
                                            <?= userEscape($user["last_activity"]) ?>
                                        <?php else: ?>
                                            No activity recorded
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="user-actions">

                                        <form
                                            class="user-action-form"
                                            method="POST"
                                            action="users.php"
                                            onsubmit="return confirm('Accept this user account?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= userEscape($csrf_token) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user["id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="accept"
                                            >

                                            <button
                                                class="user-action-btn user-accept"
                                                type="submit"
                                                <?= $status === "Accepted" ? "disabled" : "" ?>
                                            >
                                                Accept
                                            </button>
                                        </form>

                                        <form
                                            class="user-action-form"
                                            method="POST"
                                            action="users.php"
                                            onsubmit="return confirm('Reject this user account?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= userEscape($csrf_token) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$user["id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="reject"
                                            >

                                            <button
                                                class="user-action-btn user-reject"
                                                type="submit"
                                                <?= $status === "Rejected" ? "disabled" : "" ?>
                                            >
                                                Reject
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>
