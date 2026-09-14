<?php
session_start();
require_once '../config/db.php';

/** @var mysqli $conn */

if (isset($_POST['register_btn'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);

    $user_data = "full_name=" . urlencode($full_name) . "&phone=" . urlencode($phone) . "&email=" . urlencode($email) . "&role=" . urlencode($role) . "&province=" . urlencode($province) . "&address=" . urlencode($address) . "&country=" . urlencode($country);

    $digits_only = str_replace('+', '', $phone);
    if (!ctype_digit($digits_only) || strlen($digits_only) !== 10) {
        header("Location: ../register.php?error=Phone must be exactly 10 digits&" . $user_data);
        exit();
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $clean_data = str_replace("email=" . urlencode($email), "email=", $user_data);
        header("Location: ../register.php?error=Invalid email format. Use example@domain.com&" . $clean_data);
        exit();
    }

    $email_safe = mysqli_real_escape_string($conn, $email);
    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email_safe' LIMIT 1");

    if (mysqli_num_rows($check_email) > 0) {
        $clean_data = str_replace("email=" . urlencode($email), "email=", $user_data);
        header("Location: ../register.php?error=This email is already registered&" . $clean_data);
        exit();
    }

    $check_phone = mysqli_query($conn, "SELECT user_id FROM users WHERE phone = '$phone' LIMIT 1");
    if (mysqli_num_rows($check_phone) > 0) {
        $clean_data = str_replace("phone=" . urlencode($phone), "phone=", $user_data);
        header("Location: ../register.php?error=This phone number is already registered&" . $clean_data);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $status = ($role === 'shelter') ? 'pending' : 'active';
    $doc_name = "";

    if ($role === 'shelter') {
        if (!isset($_FILES['verification_doc']) || $_FILES['verification_doc']['error'] === 4) {
            header("Location: ../register.php?error=Shelter registration requires a verification document&" . $user_data);
            exit();
        }

        $target_dir = __DIR__ . "/../assets/shelterdocs/verification/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = pathinfo($_FILES['verification_doc']['name'], PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($_FILES['verification_doc']['name'], PATHINFO_EXTENSION));
        $filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename);
        $new_filename = $filename . "_" . time() . "." . $extension;

        if (move_uploaded_file($_FILES['verification_doc']['tmp_name'], $target_dir . $new_filename)) {
            $doc_name = $new_filename;
        } else {
            header("Location: ../register.php?error=File upload failed&" . $user_data);
            exit();
        }
    }

    $sql = "INSERT INTO users (full_name, email, phone, password, role, status, province, address, country, verification_doc)
            VALUES ('$full_name', '$email', '$phone', '$hashed_password', '$role', '$status', '$province', '$address', '$country', '$doc_name')";

    if (mysqli_query($conn, $sql)) {
        $msg = ($status === 'pending') ? "registered_pending" : "success";
        header("Location: ../login.php?registration=$msg");
        exit();
    } else {
        header("Location: ../register.php?error=Database error: " . mysqli_error($conn) . "&" . $user_data);
        exit();
    }

} else {
    header("Location: ../register.php");
    exit();
}