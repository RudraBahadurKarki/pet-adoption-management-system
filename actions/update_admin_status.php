<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../login.php?error=Unauthorized");
    exit();
}

if (isset($_POST['update_status_btn'])) {
    $target_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $super_admin_id = $_SESSION['user_id'];

    mysqli_begin_transaction($conn);

    try {

        $update_query = "UPDATE users SET status = '$new_status' WHERE user_id = '$target_id'";
        mysqli_query($conn, $update_query);

        $action_type = "ACCOUNT_STATUS_CHANGE";
        $description = "Super Admin (ID: $super_admin_id) changed Admin (ID: $target_id) status to $new_status";

        $log_query = "INSERT INTO audit_logs (user_id, action_type, description)
                      VALUES ('$super_admin_id', '$action_type', '$description')";
        mysqli_query($conn, $log_query);

        mysqli_commit($conn);
        header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "success=Status Changed");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=Transaction failed: " . $e->getMessage());
    }
    exit();
}