<?php
session_start();
require_once '../config/db.php';


/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please login to apply");
    exit();
}

if (isset($_GET['id'])) {
    $pet_id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];

    $pet_check = mysqli_query($conn, "SELECT shelter_id FROM pets WHERE id = '$pet_id'");
    $pet_data = mysqli_fetch_assoc($pet_check);

    if (!$pet_data) {
        die("Error: Pet record not found.");
    }

    if ($pet_data['shelter_id'] == $user_id) {
        header("Location: ../views/adopter/pet_details.php?id=$pet_id&error=Shelters cannot adopt their own pets");
        exit();
    }

    $check_dup = mysqli_query($conn, "SELECT id FROM adoption_applications WHERE pet_id = '$pet_id' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_dup) > 0) {
        header("Location: ../views/adopter/pet_details.php?id=$pet_id&msg=You have already applied for this pet");
        exit();
    }

    $sql = "INSERT INTO adoption_applications (pet_id, user_id, application_status) VALUES ('$pet_id', '$user_id', 'pending')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../views/adopter/pet_details.php?id=$pet_id&msg=Application submitted successfully!");
        exit();
    } else {
        die("Database Error: " . mysqli_error($conn));
    }

} else {
    header("Location: ../views/adopter/explore.php");
    exit();
}