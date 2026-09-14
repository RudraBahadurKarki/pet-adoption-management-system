<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'adopter') {
    header("Location: ../../login.php");
    exit();
}

$pet_id = isset($_GET['pet_id']) ? mysqli_real_escape_string($conn, $_GET['pet_id']) : '';

$pet_query = "SELECT pet_name FROM pets WHERE pet_id = '$pet_id'";
$pet_result = mysqli_query($conn, $pet_query);
$pet = mysqli_fetch_assoc($pet_result);

if (!$pet) {
    die("Pet not found.");
}

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/adopter.css">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="fw-bold mb-0">Adoption Application</h4>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary-subtle d-inline-block p-3 rounded-circle mb-3">
                            <i class="fas fa-heart text-primary fa-2x"></i>
                        </div>
                        <h5>Applying for: <strong><?= htmlspecialchars($pet['pet_name']) ?></strong></h5>
                    </div>

                    <form action="../../actions/submit_request_action.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="target_pet_id" value="<?= $pet_id ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Identity Document
                                (Citizenship / License) government based <span class="text-danger">*</span></label>
                            <input type="file" name="identity_doc" class="form-control rounded-4 bg-light border-0 p-3"
                                accept="image/*,.pdf" required>
                            <div class="form-text mt-2 small text-muted">
                                Please upload a clear photo of your ID for safety verification.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Why do you want to
                                adopt?</label>
                            <textarea name="application_message" class="form-control rounded-4 bg-light border-0 p-3"
                                rows="4" placeholder="Describe your home environment and experience..."
                                required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Terms & Conditions</label>
                            <div class="border rounded-4 p-3 bg-light overflow-auto"
                                style="max-height: 150px; font-size: 0.85rem;">
                                <p class="mb-2"><strong>1. Proper Care:</strong> I agree to provide adequate food,
                                    water, shelter, and medical care for the pet.</p>
                                <p class="mb-2"><strong>2. No Abuse:</strong> I understand that any form of animal
                                    cruelty or neglect is strictly prohibited.</p>
                                <p class="mb-2"><strong>3. Inspection:</strong> I consent to random wellness checks by
                                    the shelter. If the pet is found in poor condition, the shelter has the legal right
                                    to reclaim the pet immediately.</p>
                                <p class="mb-2"><strong>4. Legal Action:</strong> I acknowledge that failure to fulfill
                                    with these terms may result in legal action under animal welfare laws.</p>
                                <p class="mb-0"><strong>5. Non-Transferable:</strong> The pet cannot be sold or given
                                    away to another party without shelter notification.</p>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required>
                            <label class="form-check-label small fw-bold" for="termsCheck">
                                I agree to the terms and conditions mentioned above. <span class="text-danger">*</span>
                            </label>
                            <div class="invalid-feedback">
                                You must agree before submitting.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit_request"
                                class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                Submit Application
                            </button>
                            <a href="pet_details.php?id=<?= $pet_id ?>"
                                class="btn btn-link text-muted text-decoration-none">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>