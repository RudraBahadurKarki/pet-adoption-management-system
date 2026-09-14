<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['status']) && isset($_POST['app_id']) && isset($_POST['pet_id'])) {

    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $app_id = mysqli_real_escape_string($conn, $_POST['app_id']);
    $pet_id = mysqli_real_escape_string($conn, $_POST['pet_id']);
    $user_id = $_SESSION['user_id'];

    $check_owner = mysqli_query($conn, "SELECT pet_id FROM pets WHERE pet_id = '$pet_id' AND current_owner_id = '$user_id'");

    if (mysqli_num_rows($check_owner) > 0) {

        $update_main = "UPDATE adoption_applications SET
                        app_status = '$status',
                        is_viewed = 0
                        WHERE app_id = '$app_id'";

        if (mysqli_query($conn, $update_main)) {

            $get_app_info = mysqli_query($conn, "SELECT applicant_id FROM adoption_applications WHERE app_id = '$app_id'");
            $app_info = mysqli_fetch_assoc($get_app_info);
            $applicant_id = $app_info['applicant_id'];

            $action_type = ($status === 'approved') ? "ADOPTION_APPROVE" : "ADOPTION_REJECT";
            $log_desc = "Shelter ID #$user_id $status application ID #$app_id for Pet ID #$pet_id";

            mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description)
                                VALUES ('$user_id', '$action_type', '$log_desc')");

            if ($status === 'approved') {

                $update_pet = "UPDATE pets SET
                               adoption_status = 'adopted',
                               final_adopter_id = '$applicant_id'
                               WHERE pet_id = '$pet_id'";

                mysqli_query($conn, $update_pet);

                $auto_reject = "UPDATE adoption_applications SET
                                app_status = 'rejected',
                                is_viewed = 0
                                WHERE target_pet_id = '$pet_id'
                                AND app_id != '$app_id'
                                AND app_status = 'pending'";

                mysqli_query($conn, $auto_reject);

                $msg = "Adoption Approved! The pet has been marked as adopted and other applicants notified.";
            } else {
                $msg = "Application rejected successfully.";
            }

            header("Location: ../views/shelter/verify_adoptions.php?success=" . urlencode($msg));
            exit();
        } else {
            header("Location: ../views/shelter/verify_adoptions.php?error=" . urlencode("Database error: " . mysqli_error($conn)));
            exit();
        }
    } else {
        header("Location: ../views/shelter/verify_adoptions.php?error=Unauthorized access");
        exit();
    }
} else {
    header("Location: ../views/shelter/verify_adoptions.php?error=Invalid request parameters");
    exit();
}
?>