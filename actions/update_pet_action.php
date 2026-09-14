<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'shelter') {
    header("Location: ../../login.php?error=Unauthorized");
    exit();
}

if (isset($_POST['update_pet_btn'])) {
    $pet_id = mysqli_real_escape_string($conn, $_POST['pet_id']);
    $shelter_id = $_SESSION['user_id'];
    $name = trim(mysqli_real_escape_string($conn, $_POST['pet_name']));
    $species = trim(mysqli_real_escape_string($conn, $_POST['species']));
    $breed = trim(mysqli_real_escape_string($conn, $_POST['breed']));
    $age = trim(mysqli_real_escape_string($conn, $_POST['age']));
    $gender = trim(mysqli_real_escape_string($conn, $_POST['gender']));
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));

    if (empty($name) || empty($species) || empty($breed) || empty($age) || empty($gender) || empty($description)) {
        header("Location: ../views/shelter/edit_pet.php?id=$pet_id&error=All fields are required.");
        exit();
    }

    $verify_q = mysqli_query($conn, "SELECT image_path FROM pets WHERE pet_id = '$pet_id' AND current_owner_id = '$shelter_id'");
    $current_pet_data = mysqli_fetch_assoc($verify_q);
    if (!$current_pet_data) {
        die("Unauthorized access or Pet not found.");
    }

    $image_sql = "";

    if (!empty($_FILES['pet_image']['name'])) {
        $original_name = $_FILES['pet_image']['name'];
        $target_dir = "../assets/images/pets/";

        $clean_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
        $new_filename = time() . "_" . $clean_name;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
            $image_sql = ", image_path = '$new_filename'";

            $old_image = $current_pet_data['image_path'];
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        }
    }

    $sql = "UPDATE pets SET
                pet_name = '$name',
                species = '$species',
                breed = '$breed',
                age = '$age',
                gender = '$gender',
                description = '$description',
                adoption_status = 'available'
                $image_sql
            WHERE pet_id = '$pet_id' AND current_owner_id = '$shelter_id'";

    if (mysqli_query($conn, $sql)) {
        $log_user_id = $_SESSION['user_id'];
        $log_desc = "Shelter updated details for pet: $name (ID: $pet_id).";
        mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description)
                            VALUES ('$log_user_id', 'SHELTERPET_UPDATE', '$log_desc')");

        header("Location: ../views/shelter/manage_my_pets.php?msg=Pet updated successfully");
        exit();
    } else {
        header("Location: ../views/shelter/edit_pet.php?id=$pet_id&error=Database Error");
        exit();
    }
}