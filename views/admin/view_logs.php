<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

$user_role = $_SESSION['user_role'] ?? '';

if ($user_role !== 'super_admin') {
    header("Location: dashboard.php?error=Unauthorized Access");
    exit();
}

include '../../includes/header.php';

$query = "SELECT al.*, u.full_name, u.role, u.province, u.address, u.phone
          FROM audit_logs al
          JOIN users u ON al.user_id = u.user_id
          ORDER BY al.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-shield-alt text-danger me-2"></i>System Audit Logs</h2>
            <p class="text-muted small mb-0">Full security trail with shelter location and contact mapping.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge bg-dark rounded-pill px-3 py-2">
            Total Log Entries: <?= mysqli_num_rows($result) ?>
        </span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th class="ps-4 py-3">Timestamp</th>
                        <th class="py-3">Administrator / Shelter</th>
                        <th class="py-3">Origin (Location)</th>
                        <th class="py-3">Action Type</th>
                        <th class="py-3">Log Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($log = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4 text-muted small" style="width: 150px;">
                                    <i class="far fa-clock me-1 text-primary"></i>
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?><br>
                                    <span
                                        class="ps-4 text-dark fw-bold"><?= date('h:i A', strtotime($log['created_at'])) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($log['full_name']) ?></div>
                                    <div class="text-muted small"><i
                                            class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($log['phone'] ?? 'N/A') ?>
                                    </div>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill"
                                        style="font-size: 0.6rem;">
                                        <?= strtoupper($log['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><i
                                            class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($log['province'] ?? 'Unknown') ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($log['address'] ?? 'Nepal') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $action = strtolower($log['action_type']);
                                    $badgeClass = match (true) {
                                        str_contains($action, 'delete') => 'bg-danger',
                                        str_contains($action, 'update') => 'bg-warning text-dark',
                                        str_contains($action, 'insert') || str_contains($action, 'add') => 'bg-success',
                                        str_contains($action, 'approve') || str_contains($action, 'accept') => 'bg-primary',
                                        str_contains($action, 'reject') || str_contains($action, 'decline') => 'bg-dark',
                                        str_contains($action, 'verify') => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> px-3 rounded-pill small">
                                        <?= htmlspecialchars($log['action_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="p-2 rounded bg-light border-start border-3 border-dark small text-dark"
                                        style="max-width: 350px;">
                                        <?= htmlspecialchars($log['description']) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-history fa-3x text-light mb-3"></i>
                                <h5 class="text-muted">No activity logs recorded yet.</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/admin.js"></script>
