<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'shelter') {
    header("Location: ../../login.php?error=Unauthorized");
    exit();
}

$uid = $_SESSION['user_id'];

$query = "SELECT p.*,
                 u.full_name AS adopter_name,
                 u.phone AS adopter_phone,
                 u.province AS adopter_province,
                 aa.identity_doc
          FROM pets p
          LEFT JOIN users u ON p.final_adopter_id = u.user_id
          LEFT JOIN adoption_applications aa ON p.pet_id = aa.target_pet_id AND aa.app_status = 'approved'
          WHERE p.current_owner_id = ?
          ORDER BY p.pet_id DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/shelter.css">

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">
                <i class="fas fa-paw me-2"></i>My Pet Listings
            </h2>
            <p class="text-muted mb-0">You are managing <strong><?= mysqli_num_rows($result) ?></strong> active
                listings.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
            <a href="add_pet.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i>Add New Pet
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div id="auto-alert" class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary uppercase small fw-bold">
                    <tr>
                        <th class="ps-4 py-3">Pet Profile</th>
                        <th class="py-3">Details</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Adopter Information</th>
                        <th class="text-center py-3 pe-4">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($pet = mysqli_fetch_assoc($result)):
                            $status = strtolower($pet['adoption_status'] ?? 'pending');
                            $img_path = "../../assets/images/pets/" . ($pet['image_path'] ?: 'default-pet.png');

                            $badgeStyle = match ($status) {
                                'available' => 'bg-success-subtle text-success border-success',
                                'adopted' => 'bg-primary text-white border-primary',
                                'pending' => 'bg-warning-subtle text-warning border-warning',
                                'rejected' => 'bg-danger-subtle text-danger border-danger',
                                default => 'bg-light text-muted'
                            };
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $img_path ?>" class="rounded-3 border shadow-sm"
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                            onerror="this.src='../../assets/images/default-pet.png'">
                                        <div class="ms-3">
                                            <div class="fw-bold text-dark h6 mb-0"><?= htmlspecialchars($pet['pet_name']) ?>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($pet['breed']) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="small text-dark fw-semibold"><?= ucfirst($pet['species']) ?></div>
                                    <div class="text-muted extra-small"><?= ucfirst($pet['gender']) ?></div>
                                </td>

                                <td>
                                    <span class="badge border rounded-pill px-3 py-2 <?= $badgeStyle ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($status === 'adopted' && !empty($pet['adopter_name'])): ?>
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold small text-dark">
                                                <?= htmlspecialchars($pet['adopter_name']) ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger ms-1"
                                                    style="font-size: 9px;"><?= htmlspecialchars($pet['adopter_province']) ?></span>
                                            </div>
                                            <div class="text-muted extra-small mb-1" style="font-size: 11px;">
                                                <i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($pet['adopter_phone']) ?>
                                            </div>
                                            <?php if (!empty($pet['identity_doc'])): ?>
                                                <a href="../../assets/adopter_doc/<?= $pet['identity_doc'] ?>" target="_blank"
                                                    class="text-primary extra-small fw-bold text-decoration-none">
                                                    <i class="fas fa-file-pdf me-1"></i>View Adopter Doc
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted italic small">No adopter yet</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($status === 'available'): ?>
                                            <a href="edit_pet.php?id=<?= $pet['pet_id'] ?>"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="../../actions/update_status.php?id=<?= $pet['pet_id'] ?>&status=pending"
                                                class="btn btn-sm btn-warning rounded-pill px-3">
                                                <i class="fas fa-hand-paper me-1"></i>Hold
                                            </a>
                                            <a href="../../actions/delete_pet.php?id=<?= $pet['pet_id'] ?>"
                                                onclick="return confirm('Delete this pet?')"
                                                class="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                style="width:32px; height:32px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php elseif ($status === 'adopted'): ?>
                                            <button
                                                class="btn btn-sm btn-light border rounded-pill px-3 text-success fw-bold disabled">
                                                <i class="fas fa-check-double me-1"></i> Adopted
                                            </button>
                                        <?php else: ?>
                                            <a href="../../actions/update_status.php?id=<?= $pet['pet_id'] ?>&status=available"
                                                class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                Release Hold
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted mt-3">You haven't posted any pets yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/shelter.js"></script>