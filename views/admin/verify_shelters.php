<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

$user_role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$admin_province = $_SESSION['province'] ?? '';
$is_super = ($user_role === 'super_admin');

if ($user_role !== 'admin' && $user_role !== 'super_admin') {
    header("Location: dashboard.php?error=Unauthorized Access");
    exit();
}

if (isset($_GET['approve_id'])) {
    $sid = mysqli_real_escape_string($conn, $_GET['approve_id']);
    $sql = "UPDATE users SET status = 'active' WHERE user_id = '$sid' AND role = 'shelter'";

    if (mysqli_query($conn, $sql)) {
        $role_title = $is_super ? "Super Admin" : "Staff Admin";
        $log_desc = "$role_title approved Shelter ID #$sid";
        mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description) VALUES ('$user_id', 'SHELTER_VERIFY', '$log_desc' )");
        header("Location: verify_shelters.php?msg=Shelter approved and activated.");
        exit();
    }
}

if (isset($_GET['reject_id'])) {
    $sid = mysqli_real_escape_string($conn, $_GET['reject_id']);

    $sql = "UPDATE users SET status = 'rejected' WHERE user_id = '$sid' AND role = 'shelter'";

    if (mysqli_query($conn, $sql)) {
        $role_title = $is_super ? "Super Admin" : "Staff Admin";
        $log_desc = "$role_title marked Shelter ID #$sid as Rejected (Documentation preserved)";

        mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description)
                            VALUES ('$user_id', 'SHELTER_REJECT', '$log_desc')");

        header("Location: verify_shelters.php?msg=Registration request marked as rejected.");
        exit();
    }
}

if ($is_super) {
    $query = "SELECT user_id, full_name, email, created_at, province, address, verification_doc
              FROM users
              WHERE role = 'shelter' AND status = 'pending'
              ORDER BY created_at DESC";
    $province_label = "All Provinces";
} else {
    $query = "SELECT user_id, full_name, email, created_at, province, address, verification_doc
              FROM users
              WHERE role = 'shelter' AND status = 'pending' AND province = '$admin_province'
              ORDER BY created_at DESC";
    $province_label = $admin_province;
}

$pending_shelters = mysqli_query($conn, $query);

include '../../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-success mb-1">
                <i class="fas fa-shield-alt me-2"></i>Shelter Verification
            </h2>
            <p class="text-muted mb-0">Strict Review Mode: <span
                    class="badge bg-success"><?= htmlspecialchars($province_label) ?></span></p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="dashboard.php" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th class="ps-4 py-3">Shelter Info</th>
                        <th class="py-3">Contact & Location</th>
                        <th class="py-3 text-center">Verification Doc</th>
                        <th class="py-3 text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($pending_shelters) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($pending_shelters)): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['full_name']) ?></div>
                                    <small class="badge bg-light text-muted border">ID: #<?= $row['user_id'] ?></small>
                                </td>
                                <td>
                                    <div class="small"><i
                                            class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($row['email']) ?>
                                    </div>
                                    <div class="small text-capitalize">
                                        <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                        <?= htmlspecialchars($row['address']) ?>, <?= htmlspecialchars($row['province']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['verification_doc'])): ?>
                                        <?php
                                        $file_path = "../../assets/shelterdocs/verification/" . $row['verification_doc'];
                                        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                                        $icon = in_array($ext, ['jpg', 'jpeg', 'png']) ? 'fa-image' : 'fa-file-pdf';
                                        ?>
                                        <a href="<?= $file_path ?>" target="_blank"
                                            class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                            <i class="fas <?= $icon ?> me-1"></i> View Doc
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger small fw-bold"><i class="fas fa-exclamation-triangle"></i> Missing
                                            Doc</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="verify_shelters.php?approve_id=<?= $row['user_id'] ?>"
                                            class="btn btn-sm btn-success rounded-pill px-3 shadow-sm"
                                            onclick="return confirm('APPROVE: Are you sure this shelter is genuine have you check the doc so that no shelter can be fake ?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="verify_shelters.php?reject_id=<?= $row['user_id'] ?>"
                                            class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm"
                                            onclick="return confirm('REJECT: Set status to rejected?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-clipboard-check fa-3x text-light mb-3"></i>
                                <h5 class="text-muted">No pending shelter applications.</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="flex-grow-1">
    <div class="container mt-5 mb-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>


<script src="../../assets/js/admin.js"></script>