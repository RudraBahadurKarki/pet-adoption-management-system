<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../config/db.php';

$timeout_limit = 1800;

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_limit)) {
        session_unset();
        session_destroy();
        header("Location: /pet_adoption_system/login.php?error=timeout");
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>

<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$current_page = basename($_SERVER['PHP_SELF']);

$user_display_name = "Guest";
$user_province = $_SESSION['province'] ?? "";
$user_country = $_SESSION['country'] ?? "";
$role = $_SESSION['user_role'] ?? '';
$uid = $_SESSION['user_id'] ?? null;

$is_super_admin = ($role === 'admin' && $uid == 1);

if ($uid) {
    if (!isset($_SESSION['full_name']) || !isset($_SESSION['province'])) {
        $user_res = mysqli_query($conn, "SELECT full_name, province, country FROM users WHERE user_id = '$uid'");
        if ($user_res && $row = mysqli_fetch_assoc($user_res)) {
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['province'] = $row['province'];
            $_SESSION['country'] = $row['country'];
            $user_province = $row['province'];
            $user_country = $row['country'];
        }
    }
    $user_display_name = $_SESSION['full_name'] ?? 'User';
}

$home_url = "/pet_adoption_system/index.php";

if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            $home_url = "/pet_adoption_system/views/admin/dashboard.php";
            break;
        case 'super_admin':
            $home_url = "/pet_adoption_system/views/admin/dashboard.php";
            break;
        case 'shelter':
            $home_url = "/pet_adoption_system/views/shelter/dashboard.php";
            break;
        case 'adopter':
            $home_url = "/pet_adoption_system/views/adopter/explore.php";
            break;
    }
}

$adopter_notif_count = 0;

if ($role === 'adopter') {
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM adoption_applications WHERE applicant_id = '$uid' AND is_viewed = 0 AND app_status != 'pending'");
    $adopter_notif_count = mysqli_fetch_assoc($res)['total'] ?? 0;
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Adoption Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/pet_adoption_system/assets/css/header.css">
</head>


<body class="d-flex flex-column min-vh-100"></body>
    <nav class="navbar navbar-expand-lg shadow-sm sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand" href="/pet_adoption_system/index.php">
                <i class="fas fa-paw me-2"></i>Pet Adoption Management System
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= $home_url ?>">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link px-3"
                            href="/pet_adoption_system/views/adopter/explore.php"><i class="fas fa-search me-1"></i>Find
                            Pets</a></li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($uid): ?>
                        <li class="nav-item d-none d-lg-block me-3 text-end">
                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                <?= htmlspecialchars($user_province) ?>
                                <?= $user_country ? ', ' . htmlspecialchars($user_country) : '' ?>
                            </small>
                            <strong class="text-dark">Hello, <?= htmlspecialchars($user_display_name) ?></strong>
                        </li>

                        <?php if ($role === 'adopter'): ?>
                            <li class="nav-item">
                                <a class="nav-link role-btn px-3 py-2"
                                    href="/pet_adoption_system/views/adopter/my_applications.php">
                                    <i class="fas fa-paw me-1"></i> My Applications
                                    <?php if ($adopter_notif_count > 0): ?>
                                        <span class="badge rounded-pill bg-danger blink-notif"><?= $adopter_notif_count ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-danger btn-auth btn-sm py-2 px-3 rounded-pill fw-bold"
                                href="/pet_adoption_system/actions/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item px-1">
                            <a class="nav-link btn-auth btn-login px-4" href="/pet_adoption_system/login.php">Login</a>
                        </li>
                        <li class="nav-item px-1">
                            <a class="nav-link btn-auth btn-join px-4" href="/pet_adoption_system/register.php">Join Us</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<script src="assets/js/main.js"></script>