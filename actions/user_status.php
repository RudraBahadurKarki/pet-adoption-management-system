<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.php?error=Unauthorized");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $user_id_to_change = mysqli_real_escape_string($conn, $_GET['id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);
    $admin_id = $_SESSION['user_id'];
    $admin_name = $_SESSION['full_name'] ?? 'Admin';

    if ($user_id_to_change == $admin_id || $user_id_to_change == 1) {
        header("Location: ../views/admin/manage_users.php?error=Cannot modify protected system account");
        exit();
    }

    $sql = "UPDATE users SET status = '$new_status' WHERE user_id = '$user_id_to_change'";

    if (mysqli_query($conn, $sql)) {

        $ip = $_SERVER['REMOTE_ADDR'];
        $raw_log_msg = "Admin ($admin_name) changed User ID #$user_id_to_change status to $new_status";
        $safe_log_msg = mysqli_real_escape_string($conn, $raw_log_msg);


        $log_sql = "INSERT INTO audit_logs (user_id, action_type, description, ip_address)
                    VALUES ('$admin_id', 'USER_STATUS_CHANGE', '$safe_log_msg', '$ip')";

        if (mysqli_query($conn, $log_sql)) {
            header("Location: ../views/admin/manage_users.php?msg=User status updated to $new_status");
        } else {

            header("Location: ../views/admin/manage_users.php?msg=Status updated (Log Entry Failed)");
        }
    } else {
        header("Location: ../views/admin/manage_users.php?error=Database update failed");
    }
} else {
    header("Location: ../views/admin/manage_users.php");
}
exit();