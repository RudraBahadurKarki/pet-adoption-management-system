<?php
session_start();
require_once '../config/db.php';


/** @var mysqli $conn */

$id = mysqli_real_escape_string($conn, $_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);
$admin_id = $_SESSION['user_id'];

$desc = "Admin ID #$admin_id changed Pet ID #$id status to $status";

$sql = "UPDATE pets SET
            adoption_status = '$status'
        WHERE pet_id = '$id'";

if (mysqli_query($conn, $sql)) {
    $log_sql = "INSERT INTO audit_logs (user_id, action_type, description)
                VALUES ('$admin_id', 'PET_STATUS_CHANGE', '$desc')";

    mysqli_query($conn, $log_sql);

    header("Location: ../views/shelter/manage_my_pets.php?msg=Pet status updated to $status");
} else {
    echo "Error updating record: " . mysqli_error($conn);
}
?>