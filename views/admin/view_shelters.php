<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header("Location: ../../login.php?error=Access Denied");
    exit();
}

include '../../includes/header.php';

$query = "SELECT users.id, users.full_name, users.email, users.phone,
          (SELECT COUNT(*) FROM pets WHERE pets.shelter_id = users.id) as total_pets
          FROM users
          WHERE users.role = 'shelter' AND users.status = 'active'
          ORDER BY users.full_name ASC";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Registered Shelters</h2>
            <p class="text-muted">Total active pet shelters in the system:
                <strong><?= mysqli_num_rows($result) ?></strong>
            </p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Dashboard
        </a>
    </div>

    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($shelter = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-0"><?= $shelter['full_name'] ?></h5>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2"
                                        style="font-size: 0.7rem;">Verified</span>
                                </div>
                            </div>

                            <hr class="text-light">

                            <p class="mb-1 small"><i class="fas fa-envelope text-muted me-2"></i><?= $shelter['email'] ?></p>
                            <p class="mb-3 small"><i class="fas fa-phone text-muted me-2"></i><?= $shelter['phone'] ?></p>

                            <div class="bg-light p-2 rounded-3 text-center">
                                <span class="text-muted small">Active Pet Listings:</span>
                                <h4 class="fw-bold text-primary mb-0"><?= $shelter['total_pets'] ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No active shelters found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>