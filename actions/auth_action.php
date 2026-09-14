<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';


/** @var mysqli $conn */

if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            if ($user['status'] === 'pending') {
                header("Location: ../login.php?registration=registered_pending&email=" . urlencode($email));
                exit();
            }

            if ($user['status'] === 'rejected') {
                header("Location: ../login.php?error=Your application was rejected.&email=" . urlencode($email));
                exit();
            }

            if ($user['status'] !== 'active') {
                header("Location: ../login.php?error=Account not approved.&email=" . urlencode($email));
                exit();
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['province'] = $user['province'];

            if (isset($_SESSION['redirect_url'])) {
                $target = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header("Location: ../" . $target);
                exit();
            }

            if ($user['role'] == 'super_admin' || $user['role'] == 'admin') {
                header("Location: ../views/admin/dashboard.php");
            } elseif ($user['role'] == 'shelter') {
                header("Location: ../views/shelter/dashboard.php");
            } else {
                header("Location: ../views/adopter/explore.php");
            }
            exit();

        } else {
            header("Location: ../login.php?error=Wrong password&email=" . urlencode($email));
            exit();
        }
    } else {
        header("Location: ../login.php?error=Account not found");
        exit();
    }
}
?>