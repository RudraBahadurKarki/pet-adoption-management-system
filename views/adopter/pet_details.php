<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_GET['id'])) {
    header("Location: explore.php");
    exit();
}

$pet_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'] ?? null;

$query = "SELECT pets.*,
                 users.full_name as shelter_name,
                 users.email as shelter_email,
                 users.phone as shelter_phone,
                 users.address as shelter_location,
                 users.province as shelter_province
          FROM pets
          JOIN users ON pets.current_owner_id = users.user_id
          WHERE pets.pet_id = '$pet_id' AND (pets.adoption_status = 'available' OR pets.adoption_status = 'pending')";

$result = mysqli_query($conn, $query);
$pet = mysqli_fetch_assoc($result);

if (!$pet) {
    die("<div class='container mt-5 alert alert-warning'>Pet not found or not yet approved.</div>");
}

$already_applied = false;
if ($user_id) {
    $check_query = "SELECT app_id FROM adoption_applications
                    WHERE target_pet_id = '$pet_id' AND applicant_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_query);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $already_applied = true;
    }
}

include '../../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="explore.php" class="text-decoration-none">Explore</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($pet['pet_name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 20px;">
                <img src="../../assets/images/pets/<?= htmlspecialchars($pet['image_path']) ?>" class="img-fluid w-100"
                    style="max-height: 500px; object-fit: cover;" alt="<?= htmlspecialchars($pet['pet_name']) ?>">
                <div class="card-footer bg-white border-0 p-3 text-center">
                    <small class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Location:
                        <?= htmlspecialchars($pet['shelter_location']) ?>,
                        <?= htmlspecialchars($pet['shelter_province']) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h1 class="display-5 fw-bold text-dark mb-1"><?= htmlspecialchars($pet['pet_name']) ?></h1>
                    <span class="badge bg-primary rounded-pill px-3 py-2"><?= ucfirst($pet['species']) ?></span>
                    <span
                        class="badge bg-<?= $pet['gender'] == 'Male' ? 'info' : 'danger' ?>-subtle text-dark rounded-pill px-3 py-2 ms-1">
                        <i
                            class="fas fa-<?= $pet['gender'] == 'Male' ? 'mars' : 'venus' ?> me-1"></i><?= ucfirst($pet['gender']) ?>
                    </span>
                </div>
                <div class="text-end">
                    <h4 class="text-primary fw-bold mb-0"><?= htmlspecialchars($pet['age']) ?></h4>
                    <small class="text-muted">Current Age</small>
                </div>
            </div>

            <div class="card border-0 bg-light rounded-4 p-4 mb-4">
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <small class="text-muted d-block">Breed</small>
                        <span class="fw-bold"><?= htmlspecialchars($pet['breed']) ?></span>
                    </div>
                    <div class="col-4 border-end">
                        <small class="text-muted d-block">Status</small>
                        <span class="text-success fw-bold"><?= ucfirst($pet['adoption_status']) ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Province</small>
                        <span
                            class="fw-bold text-truncate d-block px-2"><?= htmlspecialchars($pet['shelter_province']) ?></span>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mb-3">About <?= htmlspecialchars($pet['pet_name']) ?></h5>
            <p class="text-muted lh-lg mb-5"><?= nl2br(htmlspecialchars($pet['description'])) ?></p>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-start border-4 border-primary">
                <h6 class="fw-bold text-uppercase small text-primary mb-3">Shelter Information</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-subtle p-3 rounded-circle me-3">
                        <i class="fas fa-building text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($pet['shelter_name']) ?></h6>
                        <small class="text-muted fw-semibold"><?= htmlspecialchars($pet['shelter_province']) ?></small>,
                        <small class="text-muted">
                            <?= htmlspecialchars($pet['shelter_location']) ?>
                        </small>


                    </div>
                </div>
                <div class="small text-muted">
                    <p class="mb-1"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($pet['shelter_phone']) ?></p>
                    <p class="mb-0"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($pet['shelter_email']) ?>
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'adopter'): ?>
                    <?php if ($already_applied): ?>
                        <button class="btn btn-secondary btn-lg w-100 rounded-pill shadow-sm py-3" disabled>
                            <i class="fas fa-check-circle me-2"></i> Application Submitted
                        </button>
                    <?php else: ?>
                        <a href="apply_form.php?pet_id=<?= $pet['pet_id'] ?>"
                            class="btn btn-primary btn-lg w-100 rounded-pill shadow py-3 fw-bold">
                            Request to Adopt <?= htmlspecialchars($pet['pet_name']) ?>
                        </a>
                    <?php endif; ?>
                <?php elseif (isset($_SESSION['user_role'])): ?>
                    <div class="alert alert-info rounded-4 border-0">
                        <i class="fas fa-info-circle me-2"></i> Logged in as <strong><?= $_SESSION['user_role'] ?></strong>.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning rounded-4 border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Please <a href="../../login.php?redirect=views/adopter/pet_details.php?id=<?= $pet['pet_id'] ?>"
                            class="fw-bold">Login</a>
                        to adopt.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/adopter.js"></script>