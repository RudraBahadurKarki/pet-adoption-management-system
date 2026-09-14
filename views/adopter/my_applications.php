<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php?error=Please login to view your history");
    exit();
}

$uid = $_SESSION['user_id'];

mysqli_query($conn, "UPDATE adoption_applications
                     SET is_viewed = 1
                     WHERE applicant_id = '$uid'
                     AND is_viewed = 0");

$user_id = $_SESSION['user_id'];

$query = "SELECT aa.*, p.pet_name, p.image_path as pet_pic, p.species,
                 u.full_name as owner_name, u.phone as owner_phone, u.email as owner_email
          FROM adoption_applications aa
          JOIN pets p ON aa.target_pet_id = p.pet_id
          JOIN users u ON p.current_owner_id = u.user_id
          WHERE aa.applicant_id = '$user_id'
          ORDER BY aa.applied_at DESC";

$result = mysqli_query($conn, $query);

mysqli_query($conn, "UPDATE adoption_applications
                     SET is_viewed = 1
                     WHERE applicant_id = '$user_id'
                     AND app_status = 'approved'");

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">My Adoption Journey</h2>
            <p class="text-muted">Track your requests and connect with owners.</p>
        </div>
        <a href="explore.php" class="btn btn-outline-primary rounded-pill btn-sm px-3">Browse More Pets</a>
    </div>

    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($app = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="row g-0 h-100">
                            <div class="col-4">
                                <img src="../../assets/images/pets/<?= htmlspecialchars($app['pet_pic']) ?>"
                                    class="img-fluid h-100" style="object-fit: cover; min-height: 200px;"
                                    onerror="this.src='../../assets/images/default-pet.png'">
                            </div>

                            <div class="col-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title fw-bold mb-0"> <?= htmlspecialchars($app['pet_name']) ?></h5>
                                        <small class="text-muted"><?= date('M d', strtotime($app['applied_at'])) ?></small>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-paw me-1"></i> <?= ucfirst($app['species']) ?>
                                    </p>

                                    <div class="mb-3">
                                        <?php if ($app['app_status'] == 'approved'): ?>
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                                <i class="fas fa-check-circle me-1"></i> Approved
                                            </span>
                                        <?php elseif ($app['app_status'] == 'rejected'): ?>
                                            <span
                                                class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">
                                                <i class="fas fa-times-circle me-1"></i> Rejected
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">
                                                <i class="fas fa-clock me-1"></i> Pending Review
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($app['app_status'] == 'approved'): ?>
                                        <div class="alert alert-success border-0 rounded-4 py-3 px-3 mb-0 mt-auto">
                                            <h6 class="fw-bold small mb-2"><i class="fas fa-bullhorn me-2"></i>Next Steps:</h6>
                                            <p class="small mb-2">Congratulations! Your application is approved. Please contact the
                                                owner to adopt your lovely pet:</p>

                                            <div class="bg-white bg-opacity-50 p-2 rounded-3">
                                                <div class="small fw-bold text-dark">
                                                    <i class="fas fa-user-circle me-1"></i>
                                                    <?= htmlspecialchars($app['owner_name']) ?>
                                                </div>
                                                <div class="small">
                                                    <i class="fas fa-phone-alt me-1 text-primary"></i>
                                                    <a href="tel:<?= $app['owner_phone'] ?>" class="text-decoration-none fw-bold">
                                                        <?= htmlspecialchars($app['owner_phone']) ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($app['app_status'] == 'pending'): ?>
                                        <div class="mt-auto">
                                            <p class="text-muted small italic mb-0">
                                                <i class="fas fa-info-circle me-1"></i> Please wait while the shelter verifies your
                                                request.
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm p-5 rounded-4 bg-white">
                    <i class="fas fa-heart text-light fa-3x mb-3"></i>
                    <h4>Your journey hasn't started yet</h4>
                    <p class="text-muted">Find a pet you love and submit an application to see it here.</p>
                    <a href="explore.php" class="btn btn-primary rounded-pill mt-3 px-4 shadow-sm">Start Exploring</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>