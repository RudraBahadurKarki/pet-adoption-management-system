<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['add_pet_btn'])) {



    if (!isset($_SESSION['user_id'])) {
        die("Error: User session not found. Please log in again.");
    }

    $shelter_id = $_SESSION['user_id'];



    $name = mysqli_real_escape_string($conn, $_POST['pet_name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);



    if ($_FILES['pet_image']['error'] !== 0) {
        die("Image Upload Error Code: " . $_FILES['pet_image']['error']);
    }

    $original_image_name = $_FILES['pet_image']['name'];



    $duplicate_check_sql = "
        SELECT id
        FROM pets
        WHERE image_path LIKE '%_" . mysqli_real_escape_string($conn, $original_image_name) . "'
        LIMIT 1
    ";
    $duplicate_result = mysqli_query($conn, $duplicate_check_sql);

    if (mysqli_num_rows($duplicate_result) > 0) {
        die("Error: Duplicate image detected. This animal image already exists in the system.");
    }



    $image_name = time() . '_' . $original_image_name;
    $target_path = "../assets/images/pets/" . $image_name;



    if (!is_dir("../assets/images/pets/")) {
        mkdir("../assets/images/pets/", 0777, true);
    }



    if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_path)) {

        $sql = "
            INSERT INTO pets
            (current_owner_id, pet_name, species, breed, age, description, image_path, adoption_status)
            VALUES
            ('$shelter_id', '$name', '$species', '$breed', '$age', '$description', '$image_name', 'pending')
        ";

        if (mysqli_query($conn, $sql)) {
            header("Location: /pet_adoption_system/views/shelter/manage_my_pets.php?msg=Pet added successfully! Waiting for approval.");
            exit();
        } else {
            die("Database Error: " . mysqli_error($conn));
        }

    } else {
        die("Error: Could not move uploaded file. Check folder permissions.");
    }

} else {
    header("Location: /pet_adoption_system/views/shelter/add_pet.php");
    exit();
}
?>