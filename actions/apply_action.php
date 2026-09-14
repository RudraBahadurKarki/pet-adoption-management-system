<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

if (isset($_GET['pet_id']) && $_SESSION['user_role'] == 'adopter') {
    $pet_id = $_GET['pet_id'];
    $user_id = $_SESSION['user_id'];

    // Prevent duplicate applications
    $check = mysqli_query($conn, "SELECT * FROM applications WHERE user_id = '$user_id' AND target_id = '$pet_id' AND type = 'adoption'");

    if (mysqli_num_rows($check) == 0) {
        $sql = "INSERT INTO applications (user_id, type, target_id, status) VALUES ('$user_id', 'adoption', '$pet_id', 'pending')";
        mysqli_query($conn, $sql);
        header("Location: ../views/adopter/my_applications.php?msg=Application Submitted!");
    } else {
        header("Location: ../views/adopter/explore.php?error=Already applied for this pet.");
    }
}
?>