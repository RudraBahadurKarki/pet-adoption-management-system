<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'shelter') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$shelter_province = $_SESSION['province'] ?? 'Your Province';


$query = "SELECT aa.*, p.pet_name, p.image_path, p.breed, p.pet_id,
                 u.full_name as adopter_name, u.phone as adopter_phone,
                 u.province as adopter_province, u.address as adopter_address
          FROM adoption_applications aa
          JOIN pets p ON aa.target_pet_id = p.pet_id
          JOIN users u ON aa.applicant_id = u.user_id
          WHERE p.current_owner_id = '$user_id'
          AND aa.app_status = 'pending'
          ORDER BY aa.applied_at DESC";

$result = mysqli_query($conn, $query);

include '../../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="../../assets/css/shelter.css">


<div class="container mt-5 mb-5">
    <div class="border-bottom pb-3 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-primary mb-1">
                    <i class="fas fa-check-circle me-2"></i>Verify Adoptions
                </h2>
                <p class="text-muted small mb-0">
                    Managing requests for pets in <strong><?= htmlspecialchars($shelter_province) ?></strong>
                </p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div id="status-alert" class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div id="status-alert" class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive card border-0 shadow-sm rounded-4 p-3">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Pet Details</th>
                    <th>Adopter Info</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                    $img = $row['image_path'];
                                    $path = "../../assets/images/pets/$img";
                                    if (empty($img) || !file_exists($path)) {
                                        $path = "../../assets/images/default-pet.png";
                                    }
                                    ?>
                                    <img src="<?= $path ?>" class="rounded-3 me-3"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['pet_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['breed'] ?: 'Mixed') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['adopter_name']) ?></div>
                                <small class="text-muted"><i
                                        class="fas fa-phone me-1"></i><?= htmlspecialchars($row['adopter_phone']) ?></small>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary rounded-pill px-4 btn-sm shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?= $row['app_id'] ?>">
                                    Review Request
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="viewModal<?= $row['app_id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0">
                                        <h5 class="fw-bold mb-0">Adoption Review</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="d-flex align-items-center mb-4 p-3 bg-primary-subtle rounded-4">
                                            <img src="<?= $path ?>" class="pet-thumb-modal me-3 shadow-sm">
                                            <div>
                                                <h4 class="fw-bold mb-0"><?= htmlspecialchars($row['pet_name']) ?></h4>
                                                <p class="text-muted mb-0">Requested by
                                                    <?= htmlspecialchars($row['adopter_name']) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="small text-uppercase fw-bold text-muted mb-2">Adopter Location</label>
                                            <div class="p-2 border rounded-3 bg-light">
                                                <div class="small">
                                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                    <strong>Province:</strong>
                                                    <?= htmlspecialchars($row['adopter_province'] ?? 'N/A') ?>
                                                </div>
                                                <div class="small mt-1">
                                                    <i class="fas fa-home text-secondary me-2"></i>
                                                    <strong>Address:</strong>
                                                    <?= htmlspecialchars($row['adopter_address'] ?? 'No address provided') ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="small text-uppercase fw-bold text-muted mb-2">Adopter Document verify
                                                this properly </label>
                                            <?php if (!empty($row['identity_doc'])): ?>
                                                <div class="d-flex align-items-center p-2 border rounded-3 bg-light">
                                                    <i class="fas fa-file-pdf text-danger fs-4 me-3"></i>
                                                    <div class="flex-grow-1">
                                                        <span class="small fw-bold d-block text-truncate"
                                                            style="max-width: 200px;"><?= htmlspecialchars($row['identity_doc']) ?></span>
                                                    </div>
                                                    <a href="../../assets/adopter_doc/<?= htmlspecialchars($row['identity_doc']) ?>"
                                                        target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted small italic p-2 border rounded-3">No document uploaded.
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-4">
                                            <label class="small text-uppercase fw-bold text-muted mb-2">Adopter's
                                                Message</label>
                                            <div class="bg-light p-3 rounded-4 border-start border-4 border-primary italic">
                                                "<?= nl2br(htmlspecialchars($row['application_message'] ?? 'No message provided.')) ?>"
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 text-center">
                                            <form action="../../actions/approve_adoption.php" method="POST">
                                                <input type="hidden" name="app_id" value="<?= $row['app_id'] ?>">
                                                <input type="hidden" name="pet_id" value="<?= $row['pet_id'] ?>">
                                                <button type="submit" name="status" value="approved"
                                                    class="btn btn-success btn-lg rounded-pill fw-bold w-100 mb-2">
                                                    <i class="fas fa-check-circle me-2"></i>Approve Adoption
                                                </button>
                                                <button type="submit" name="status" value="rejected"
                                                    class="btn btn-outline-danger border-0 rounded-pill w-100"
                                                    onclick="return confirm('Are you sure you want to reject this application?')">
                                                    Reject Application
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <h5 class="text-muted">No pending applications for your pets.</h5>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/shelter.js"></script>
