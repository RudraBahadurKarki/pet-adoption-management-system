<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

/** @var mysqli $conn */

if (isset($_POST['submit_request'])) {

    $applicant_id = $_SESSION['user_id'] ?? null;
    $target_pet_id = $_POST['target_pet_id'] ?? null;
    $message = mysqli_real_escape_string($conn, $_POST['application_message'] ?? '');

    $id_filename = null;
    if (isset($_FILES['identity_doc']) && $_FILES['identity_doc']['error'] === 0) {
        $upload_dir = '../assets/adopter_doc/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['identity_doc']['name'], PATHINFO_EXTENSION);
        $id_filename = "ID_" . $applicant_id . "_" . $target_pet_id . "_" . time() . "." . $file_ext;
        $target_path = $upload_dir . $id_filename;

        if (!move_uploaded_file($_FILES['identity_doc']['tmp_name'], $target_path)) {
            die("ERROR: Failed to save the uploaded document.");
        }
    } else {
        die("ERROR: Identity Document is required for adoption.");
    }

    if (!$applicant_id || !$target_pet_id) {
        die("ERROR: Missing IDs. User: $applicant_id, Pet: $target_pet_id. Please try again from the pet details page.");
    }

    $sql = "INSERT INTO adoption_applications (applicant_id, target_pet_id, application_message, identity_doc, app_status)
            VALUES ('$applicant_id', '$target_pet_id', '$message', '$id_filename', 'pending')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../views/adopter/explore.php?success=Application submitted successfully!");
        exit();
    } else {
        die("DATABASE ERROR: " . mysqli_error($conn));
    }
} else {
    header("Location: ../views/adopter/explore.php");
    exit();
}
?>