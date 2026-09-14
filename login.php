<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

if (isset($_GET['redirect'])) {
    $_SESSION['redirect_url'] = $_GET['redirect'];
}

$saved_email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

include('includes/header.php');
?>

<div class="d-flex flex-column min-vh-100">
    <div class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="row justify-content-center w-100">
            <div class="col-md-8 col-lg-7 col-xl-5">

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-user-lock me-2"></i>Welcome Back</h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="actions/auth_action.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i
                                            class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-0"
                                        value="<?= $saved_email ?>" required placeholder="Enter your email">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i
                                            class="fas fa-key text-primary"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-0"
                                        placeholder="••••••••" required>
                                </div>
                            </div>

                            <button type="submit" name="login_btn"
                                class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm mb-3">
                                Sign In
                            </button>
                        </form>
                    </div>

                    <div class="card-footer bg-light border-0 text-center py-3">
                        <p class="mb-0 small text-muted">New here? <a href="register.php"
                                class="text-primary fw-bold text-decoration-none">Create an Account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php'); ?>
</div>

<div class="modal fade" id="regSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-5">
                <div id="modalIconContainer" class="mb-4"></div>
                <h4 class="fw-bold text-dark" id="modalTitle"></h4>
                <p class="text-muted" id="modalMessage"></p>
                <button type="button" class="btn btn-primary rounded-pill px-5 mt-3" data-bs-dismiss="modal">Got
                    it!</button>
            </div>
        </div>
    </div>
</div>


<script src="assets/js/main.js"></script>