<?php
ini_set('session.cookie_path', '/pet_adoption_system/');
session_start();

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header("Location: ../../login.php?error=Access Denied");
    exit();
}

require_once '../../config/db.php';


/** @var mysqli $conn */


$is_super = ($_SESSION['user_role'] === 'super_admin');
$admin_label = ($is_super) ? 'COUNTRY SUPER ADMIN' : 'PROVINCIAL ADMIN';
$admin_province = $_SESSION['province'] ?? '';
$safe_p = mysqli_real_escape_string($conn, $admin_province);

$shelter_q = "SELECT COUNT(*) as total FROM users WHERE role = 'shelter' AND status = 'pending'";
if (!$is_super) {
    $shelter_q .= " AND province = '$safe_p'";
}
$pending_shelters = mysqli_fetch_assoc(mysqli_query($conn, $shelter_q))['total'] ?? 0;
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
$count_data = mysqli_fetch_assoc($count_query);
$has_pending = $count_data['total'] > 0;
$admin_count_q = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$total_provincial_admins = mysqli_fetch_assoc(mysqli_query($conn, $admin_count_q))['total'] ?? 0;

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin.css">

<div class="admin-header shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-center text-md-start">
                <h1 class="h4 fw-bold mb-1">Admin Dashboard</h1>
                <p class="text-muted small mb-0">Managing:
                    <span class="fw-bold text-dark">
                        <?= $is_super ? 'All Provinces (Nepal)' : htmlspecialchars($admin_province) . ' Province' ?>
                    </span>
                </p>
            </div>
            <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                <span class="badge <?= $is_super ? 'bg-danger' : 'bg-primary' ?> px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-user-shield me-2"></i><?= $admin_label ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if ($is_super): ?>
        <div class="super-admin-panel p-4 mb-5 shadow-sm">
            <h6 class="fw-bold text-danger mb-4 small"><i class="fas fa-globe-asia me-2"></i>SUPER ADMIN MANAGEMENT</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card stat-card border-start border-4 border-danger p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-danger text-white me-3"><i class="fas fa-user-cog"></i></div>
                            <div>
                                <h6 class="text-muted small fw-bold mb-0">PROVINCIAL ADMINS</h6>
                                <h4 class="fw-bold mb-0"><?= $total_provincial_admins ?></h4>
                            </div>
                            <div class="ms-auto">
                                <a href="manage_provincial_admins.php"
                                    class="btn btn-danger btn-sm rounded-pill px-3">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card border-start border-4 border-dark p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-dark text-white me-3"><i class="fas fa-file-invoice"></i></div>
                            <div class="overflow-hidden">
                                <h6 class="text-muted small fw-bold mb-0 text-truncate">AUDIT LOGS</h6>
                                <p class="small text-muted mb-0 text-truncate"> Activity history of shelters and admin of
                                    whole country </p>
                            </div>
                            <div class="ms-auto">
                                <a href="view_logs.php" class="btn btn-dark btn-sm rounded-pill px-3">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <h4 class="section-title">Actions</h4>
    <div class="row justify-content-center mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="card stat-card action-square shadow-sm p-4 border-top border-4 border-success text-center">
                <div class="icon-box bg-success-subtle text-success mb-3 mx-auto"
                    style="width:60px; height:60px; font-size:1.5rem;">
                    <i class="fas fa-store-alt"></i>
                </div>

                <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Shelters</h6>
                <h1 class="fw-bold mb-2">
                    <?= $pending_shelters ?>

                </h1>

                <a href="verify_shelters.php"
                    class="btn btn-success rounded-pill px-4 fw-bold w-100 mt-2 py-2 <?= ($pending_shelters > 0) ? 'blink-whole' : '' ?>">
                    Verify Applications

                </a>
            </div>
        </div>
    </div>

    <h4 class="section-title">System Resources</h4>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card stat-card shadow-sm p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-info text-white me-3"><i class="fas fa-paw"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Pet Database</h6>
                        <p class="text-muted small mb-3">Monitor all pets listed across the province.</p>
                        <a href="manage_all_pets.php" class="btn btn-outline-info btn-sm rounded-pill px-4">View
                            Records</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card shadow-sm p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-secondary text-white me-3"><i class="fas fa-users"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">User Management</h6>
                        <p class="text-muted small mb-3">Control Shelter and Adopter accounts.</p>
                        <a href="manage_users.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Manage
                            Accounts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>