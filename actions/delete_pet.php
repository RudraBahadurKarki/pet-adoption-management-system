<?php
session_start();
require_once '../config/db.php';


/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'shelter' && $_SESSION['user_role'] !== 'admin')) {
    die("Unauthorized access");
}

if (isset($_GET['id'])) {
    $pet_id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    if ($user_role === 'admin') {
        $img_query = "SELECT pet_name, image_path, adoption_status FROM pets WHERE pet_id = '$pet_id'";
        $delete_condition = "WHERE pet_id = '$pet_id'";
    } else {
        $img_query = "SELECT pet_name, image_path, adoption_status FROM pets WHERE pet_id = '$pet_id' AND current_owner_id = '$user_id'";
        $delete_condition = "WHERE pet_id = '$pet_id' AND current_owner_id = '$user_id'";
    }

    $img_result = mysqli_query($conn, $img_query);

    if (mysqli_num_rows($img_result) > 0) {
        $pet_data = mysqli_fetch_assoc($img_result);
        $p_name = $pet_data['pet_name'];


        if ($pet_data['adoption_status'] === 'adopted') {
            $loc = ($user_role === 'admin') ? "../views/admin/manage_all_pets.php" : "../views/shelter/manage_my_pets.php";
            header("Location: $loc?error=Cannot delete adopted pets");
            exit();
        }

        $filename = $pet_data['image_path'];
        $filepath = "../../" . $filename;

        if (!empty($filename) && file_exists($filepath)) {
            unlink($filepath);
        }

        $sql = "DELETE FROM pets $delete_condition";

        if (mysqli_query($conn, $sql)) {

            $action_type = ($user_role === 'admin') ? 'ADMINPET_DELETE' : 'SHELTERPET_DELETE';
            $description = "User (ID: $user_id) permanently deleted pet: $p_name (ID: $pet_id)";

            $log_sql = "INSERT INTO audit_logs (user_id, action_type, description, created_at)
                        VALUES ('$user_id', '$action_type', '$description', NOW())";
            mysqli_query($conn, $log_sql);

            $redirect_url = ($user_role === 'admin') ? "../views/admin/manage_all_pets.php" : "../views/shelter/manage_my_pets.php";
            header("Location: $redirect_url?msg=Pet deleted successfully");
            exit();
        } else {
            die("Database Error: " . mysqli_error($conn));
        }
    } else {
        $redirect_url = ($user_role === 'admin') ? "../views/admin/manage_all_pets.php" : "../views/shelter/manage_my_pets.php";
        header("Location: $redirect_url?error=Record not found or access denied");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}