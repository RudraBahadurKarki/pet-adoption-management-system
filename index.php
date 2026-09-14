<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {

    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    session_destroy();

    echo "<script>
        if (window.location.search.indexOf('error=timeout') === -1) {
            window.location.href = 'index.php';
        }
    </script>";
}

include('includes/header.php');
?>
<link rel="stylesheet" href="assets/css/style.css">

<div class="container">
    <div class="hero-section shadow-lg">
        <div class="text-center px-3">
            <h1 class="hero-title">Welcome, Guest!</h1>
            <p class="lead mb-4 fs-4">Every Pet Deserves a Loving Home. Start Your Journey Today.</p>

            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="views/adopter/explore.php" class="btn btn-primary btn-lg btn-hero shadow">
                    🐾 Adopt a Pet
                </a>
                <a href="register.php?role=shelter" class="btn btn-light btn-lg btn-hero shadow">
                    🤝 Become a shelter
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>