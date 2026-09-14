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

$shelter_id = $_SESSION['user_id'];
$pet_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

$query = "SELECT * FROM pets WHERE pet_id = '$pet_id' AND current_owner_id = '$shelter_id'";
$result = mysqli_query($conn, $query);
$pet = mysqli_fetch_assoc($result);

if (!$pet) {
    die("<div class='container mt-5 alert alert-danger rounded-4'>Error: Pet record not found or access denied.</div>");
}

$display_name = $pet['pet_name'] ?? $pet['name'] ?? '';
$display_img = $pet['image_path'] ?? $pet['image'] ?? '';
$status = $pet['adoption_status'];
$error_msg = isset($_GET['error']) ? mysqli_real_escape_string($conn, $_GET['error']) : null;

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/shelter.css">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <?php if ($error_msg): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <div class="card card-custom shadow-lg overflow-hidden">
                <div class="card-header bg-primary py-3 text-white">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Pet Listing</h4>
                </div>

                <div class="card-body p-4 p-md-5">

                    <?php if ($status === 'rejected'): ?>
                        <div class="alert alert-warning rounded-4 border-0 mb-4 shadow-sm"
                            style="background-color: #fff9e6;">
                            <h6 class="fw-bold text-dark"><i class="fas fa-comment-dots me-2"></i>Admin Feedback:</h6>
                            <p class="mb-0 small fst-italic text-muted">
                                "<?= htmlspecialchars($pet['admin_feedback'] ?? 'Please review your submission details.') ?>"
                            </p>
                        </div>
                    <?php endif; ?>

                    <form action="../../actions/update_pet_action.php" method="POST" enctype="multipart/form-data"
                        id="editPetForm">
                        <input type="hidden" name="pet_id" value="<?= $pet['pet_id'] ?>">

                        <div class="row g-3">
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold">Pet Name <span class="text-danger">*</span></label>
                                <input type="text" name="pet_name" class="form-control rounded-pill border-2"
                                    value="<?= htmlspecialchars($display_name) ?>" required>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Species <span class="text-danger">*</span></label>
                                <select name="species" class="form-select rounded-pill border-2" required>
                                    <option value="" disabled>Select Type</option>
                                    <option value="dog" <?= ($pet['species'] ?? '') == 'dog' ? 'selected' : '' ?>>Dog
                                    </option>
                                    <option value="cat" <?= ($pet['species'] ?? '') == 'cat' ? 'selected' : '' ?>>Cat
                                    </option>
                                    <option value="bird" <?= ($pet['species'] ?? '') == 'bird' ? 'selected' : '' ?>>Bird
                                    </option>
                                    <option value="others" <?= ($pet['species'] ?? '') == 'others' ? 'selected' : '' ?>>
                                        Other
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Breed <span class="text-danger">*</span></label>
                                <input type="text" name="breed" class="form-control rounded-pill border-2"
                                    value="<?= htmlspecialchars($pet['breed'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select rounded-pill border-2" required>
                                    <option value="Male" <?= ($pet['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male
                                    </option>
                                    <option value="Female" <?= ($pet['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>
                                        Female</option>
                                    <option value="Unknown" <?= ($pet['gender'] ?? '') == 'Unknown' ? 'selected' : '' ?>>
                                        Unknown</option>
                                </select>
                            </div>

                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold">Age (e.g., 2 Years, 4 Months) <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="age" class="form-control rounded-pill border-2"
                                    value="<?= htmlspecialchars($pet['age'] ?? '') ?>" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description / Story <span
                                        class="text-danger">*</span></label>
                                <textarea name="description" class="form-control rounded-4 border-2" rows="4"
                                    placeholder="Tell potential adopters about this pet..."
                                    required><?= htmlspecialchars($pet['description'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold d-block">Pet Photo</label>
                                <div
                                    class="d-flex flex-column flex-md-row align-items-center gap-4 p-3 bg-light rounded-4 border">
                                    <div class="text-center">
                                        <p class="x-small text-muted mb-1">Current</p>
                                        <img src="../../assets/images/pets/<?= $display_img ?>"
                                            class="rounded-3 shadow-sm border"
                                            style="width: 100px; height: 100px; object-fit: cover;"
                                            onerror="this.src='../../assets/images/default-pet.png'">
                                    </div>
                                    <div class="flex-grow-1 w-100">
                                        <label class="form-label small fw-bold">Replace Photo (Optional)</label>
                                        <input type="file" name="pet_image" class="form-control rounded-pill mb-1"
                                            accept="image/*">
                                        <div class="form-text x-small text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Ensure filename is unique. Leave
                                            empty to keep current.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-3 pt-2">
                                <button type="submit" name="update_pet_btn" id="submitBtn"
                                    class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                    <?= ($status === 'rejected') ? 'Resubmit Listing' : 'Save Changes' ?>
                                </button>
                                <a href="manage_my_pets.php" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>

<script src="../../assets/js/shelter.js"></script>
