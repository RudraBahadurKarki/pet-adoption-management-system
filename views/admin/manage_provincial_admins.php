<?php
session_start();
require_once '../../config/db.php';

/** @var mysqli $conn */

// Security Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../../login.php?error=Access Denied");
    exit();
}

// Fetch Admins
$query = "SELECT user_id, full_name, email, province, status, phone, created_at
          FROM users
          WHERE role = 'admin'
          ORDER BY province ASC";
$result = mysqli_query($conn, $query);

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/admin.css">

<div class="container mt-5 mb-5">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-danger text-white">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="flex-shrink-0 bg-dark rounded-circle p-3 me-3">
                <i class="fas fa-shield-alt fa-2x text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0">Nepal Provincial Center</h4>
                <p class="mb-0 opacity-75">Authenticated as Super Admin</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Regional Administrators</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-secondary small fw-bold">
                        <th class="ps-4 py-3">ADMIN NAME</th>
                        <th>PROVINCE</th>
                        <th>CONTACT & EMAIL</th>
                        <th>STATUS</th>
                        <th class="text-end pe-4">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($admin = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($admin['full_name']) ?></div>
                                    <div class="text-muted extra-small">ID: #<?= $admin['user_id'] ?></div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                        <?= htmlspecialchars($admin['province']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small"><i
                                            class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($admin['email']) ?>
                                    </div>
                                    <div class="small"><i
                                            class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($admin['phone'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($admin['status'] === 'active'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="../../actions/update_admin_status.php" method="POST"
                                        onsubmit="return confirm('Are you sure you want to change this admin\'s status?');">
                                        <input type="hidden" name="user_id" value="<?= $admin['user_id'] ?>">

                                        <?php if ($admin['status'] === 'active'): ?>
                                            <input type="hidden" name="new_status" value="pending">
                                            <button type="submit" name="update_status_btn"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="fas fa-user-slash me-1"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="active">
                                            <button type="submit" name="update_status_btn"
                                                class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="fas fa-user-check me-1"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No provincial admins found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/admin.js"></script>