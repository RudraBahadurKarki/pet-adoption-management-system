<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'shelter') {
    header("Location: ../../login.php?error=Access Denied");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn, "SELECT address, province FROM users WHERE user_id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$shelter_address = $user_data['address'] ?? 'Address Not Set';
$shelter_province = $user_data['province'] ?? 'Not Assigned';

$total_pets_q = mysqli_query($conn, "SELECT COUNT(*) as count FROM pets WHERE current_owner_id = '$user_id'");
$total_pets = mysqli_fetch_assoc($total_pets_q)['count'] ?? 0;

$adopt_q = "SELECT COUNT(*) as count FROM adoption_applications aa
            JOIN pets p ON aa.target_pet_id = p.pet_id
            WHERE p.current_owner_id = '$user_id' AND aa.app_status = 'pending'";
$pending_adoptions = mysqli_fetch_assoc(mysqli_query($conn, $adopt_q))['count'] ?? 0;

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/shelter.css">

<div class="container mt-5">
    <div class="province-banner p-5 mb-4 shadow-sm text-center">
        <h5 class="text-uppercase mb-2" style="opacity: 0.8; letter-spacing: 2px;">Shelter Dashboard</h5>
        <h1 class="fw-bold mb-0">
            <i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($shelter_address) ?>
        </h1>
    </div>

    <div class="row mb-5 justify-content-center text-center">
        <div class="col-md-10 col-lg-8">
            <a href="add_pet.php" class="btn-post-pet w-100 rounded-pill py-3 fs-3 fw-bolder shadow-lg">
                <i class="fas fa-plus-circle me-2"></i> POST A NEW PET FOR ADOPTION
            </a>
        </div>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-5">
            <div class="card stat-card shadow-sm h-100 p-4 border-top border-4 border-primary">
                <div class="icon-circle bg-primary text-white shadow-sm">
                    <i class="fas fa-file-alt fa-lg"></i>
                </div>
                <h6 class="text-muted fw-bold small text-uppercase">Adoption Requests</h6>

                <div class="d-flex align-items-center justify-content-center">
                    <h1 class="fw-bold display-4 mb-0"><?= $pending_adoptions ?></h1>
                </div>

                <p class="text-muted mt-3 mb-4">
                    <?= ($pending_adoptions > 0) ? 'New families waiting for approval.' : 'No pending applications.'; ?>
                </p>

                <a href="verify_adoptions.php"
                    class="btn btn-primary w-100 rounded-pill <?= ($pending_adoptions > 0) ? 'simple-blink' : '' ?>">
                    View Applications
                </a>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card stat-card shadow-sm h-100 p-4 border-top border-4 border-success text-center">
                <div class="icon-circle bg-success text-white shadow-sm">
                    <i class="fas fa-paw fa-lg"></i>
                </div>
                <h6 class="text-muted fw-bold small text-uppercase">Active Listings</h6>

                <div class="d-flex align-items-center justify-content-center">
                    <h1 class="fw-bold display-4 mb-0"><?= $total_pets ?></h1>
                </div>

                <p class="text-muted mt-3 mb-4">Manage your current pet listings.</p>

                <a href="manage_my_pets.php" class="btn btn-success w-100 rounded-pill">
                    Manage Pets & View Info
                </a>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>