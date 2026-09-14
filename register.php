<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$url_role = $_GET['role'] ?? 'adopter';

include('includes/header.php');
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-user-plus me-2"></i> Join the Pet Community</h4>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if (isset($_GET['error'])): ?>
                        <div id="auto-alert" class="alert alert-danger rounded-pill px-3 py-2 mb-4 small text-center">
                            <i class="fas fa-circle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="actions/register_action.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold small text-muted">Full Name</label>
                                <input type="text" name="full_name"
                                    class="form-control bg-light border-0 rounded-pill px-3" placeholder="Ram Sharma"
                                    value="<?= isset($_GET['full_name']) ? htmlspecialchars($_GET['full_name']) : '' ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Email Address</label>
                                <input type="email" name="email"
                                    class="form-control bg-light border-0 rounded-pill px-3"
                                    value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>"
                                    placeholder="name@example.com" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control bg-light border-0 rounded-pill px-3"
                                    value="<?= isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '' ?>"
                                    placeholder="9XXXXXXXXX" maxlength="10"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" pattern="[0-9]{10}"
                                    required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold small text-muted">Password</label>
                                <input type="password" name="password"
                                    class="form-control bg-light border-0 rounded-pill px-3" placeholder="--------"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Province</label>
                                <select name="province" class="form-select bg-light border-0 rounded-pill px-3"
                                    required>
                                    <option value="" disabled <?= !isset($_GET['province']) ? 'selected' : '' ?>>Select
                                        Province</option>
                                    <?php
                                    $provinces = ["Koshi", "Madhesh", "Bagmati", "Gandaki", "Lumbini", "Karnali", "Sudurpashchim"];
                                    foreach ($provinces as $p) {
                                        $selected = (isset($_GET['province']) && $_GET['province'] == $p) ? 'selected' : '';
                                        echo "<option value='$p' $selected>$p</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Country</label>
                                <input type="text" name="country"
                                    class="form-control bg-light border-0 rounded-pill px-3 shadow-sm" value="Nepal"
                                    readonly>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold small text-muted">Specific Address</label>
                                <input type="text" name="address"
                                    class="form-control bg-light border-0 rounded-pill px-3"
                                    value="<?= isset($_GET['address']) ? htmlspecialchars($_GET['address']) : '' ?>"
                                    placeholder="e.g., Koteshwor-32, Kathmandu" required>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold">I want to register as:</label>
                                <select name="role" class="form-select border-primary" id="roleSelect">
                                    <option value="adopter" <?= ($url_role === 'adopter') ? 'selected' : '' ?>>Adopter (No
                                        approval required)</option>
                                    <option value="shelter" <?= ($url_role === 'shelter') ? 'selected' : '' ?>>Shelter
                                        (Requires Admin Approval)</option>
                                </select>
                            </div>
                        </div>

                        <div id="shelter_docs" class="p-3 bg-light rounded-3 mb-4"
                            style="display:none; border: 1px dashed #6c63ff;">
                            <label class="form-label fw-bold text-primary">Shelter Verification Document</label>
                            <input type="file" name="verification_doc" id="docInput" class="form-control mb-2"
                                accept=".pdf, .jpg, .jpeg, .png">
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Upload Citizenship or Shelter
                                License (PDF/Image) government based.</small>
                        </div>

                        <button type="submit" name="register_btn"
                            class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                            Create My Account
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-light border-0 text-center py-3">
                    <p class="mb-0 small text-muted">Already have an account? <a href="login.php"
                            class="text-primary fw-bold text-decoration-none">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/register.js"></script>

<?php include('includes/footer.php'); ?>