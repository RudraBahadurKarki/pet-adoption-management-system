<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

$species_filter = isset($_GET['species']) ? mysqli_real_escape_string($conn, $_GET['species']) : '';
$province_filter = isset($_GET['province']) ? mysqli_real_escape_string($conn, $_GET['province']) : '';
$gender_filter = isset($_GET['gender']) ? mysqli_real_escape_string($conn, $_GET['gender']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT p.*, u.province, u.address
        FROM pets p
        JOIN users u ON p.current_owner_id = u.user_id
        WHERE p.adoption_status = 'available'";

if ($species_filter != '') {
    $sql .= " AND p.species = '$species_filter'";
}
if ($province_filter != '') {
    $sql .= " AND u.province = '$province_filter'";
}
if ($gender_filter != '') {
    $sql .= " AND p.gender = '$gender_filter'";
}
if ($search_query != '') {
    $sql .= " AND (p.pet_name LIKE '%$search_query%' OR p.breed LIKE '%$search_query%' OR u.address LIKE '%$search_query%')";
}

$sql .= " ORDER BY p.pet_id DESC";
$result = mysqli_query($conn, $sql);

include '../../includes/header.php';
?>


<link rel="stylesheet" href="../../assets/css/adopter.css">


<?php if (isset($_GET['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="explore-hero bg-primary text-white py-5 mb-n5"
    style="background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold">Find Your New Best Friend</h1>
        <p class="lead opacity-75">Every pet deserves a loving home. Start your journey here.</p>
    </div>
</div>

<div class="container pb-5 mb-6">
    <div class="card border-0 shadow-lg rounded-4 p-4 mb-5 position-relative" style="margin-top: -30px; z-index: 5;">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label small fw-bold text-muted ps-2">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill"><i
                            class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 rounded-end-pill"
                        placeholder="Breed, Name, or Area..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted ps-2">Species</label>
                <select name="species" class="form-select bg-light border-0 rounded-pill "
                    onchange="this.form.submit()">
                    <option value="">All Animals</option>
                    <option value="dog" <?= $species_filter == 'dog' ? 'selected' : '' ?>>Dogs</option>
                    <option value="cat" <?= $species_filter == 'cat' ? 'selected' : '' ?>>Cats</option>
                    <option value="bird" <?= $species_filter == 'bird' ? 'selected' : '' ?>>Birds</option>
                    <option value="others" <?= $species_filter == 'others' ? 'selected' : '' ?>>others</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted ps-2">Gender</label>
                <select name="gender" class="form-select bg-light border-0 rounded-pill" onchange="this.form.submit()">
                    <option value="">Any Gender</option>
                    <option value="Male" <?= $gender_filter == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $gender_filter == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Unknown" <?= $gender_filter == 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted ps-2">Province</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill"><i
                            class="fas fa-map-marker-alt text-muted"></i></span>
                    <select name="province" class="form-select bg-light border-0 rounded-end-pill"
                        onchange="this.form.submit()">
                        <option value="">All Provinces</option>
                        <option value="Koshi" <?= $province_filter == 'Koshi' ? 'selected' : '' ?>>Koshi</option>
                        <option value="Madhesh" <?= $province_filter == 'Madhesh' ? 'selected' : '' ?>>Madhesh</option>
                        <option value="Bagmati" <?= $province_filter == 'Bagmati' ? 'selected' : '' ?>>Bagmati</option>
                        <option value="Gandaki" <?= $province_filter == 'Gandaki' ? 'selected' : '' ?>>Gandaki</option>
                        <option value="Lumbini" <?= $province_filter == 'Lumbini' ? 'selected' : '' ?>>Lumbini</option>
                        <option value="Karnali" <?= $province_filter == 'Karnali' ? 'selected' : '' ?>>Karnali</option>
                        <option value="Sudurpashchim" <?= $province_filter == 'Sudurpashchim' ? 'selected' : '' ?>>
                            Sudurpashchim</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($pet = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden pet-card transition">
                        <div class="position-relative">
                            <img src="../../assets/images/pets/<?= htmlspecialchars($pet['image_path']) ?>" class="card-img-top"
                                style="height: 280px; object-fit: cover;" alt="<?= htmlspecialchars($pet['pet_name']) ?>">

                            <div class="position-absolute top-0 end-0 m-3 d-flex flex-column gap-2">
                                <span class="badge bg-white text-primary rounded-pill shadow-sm px-3 py-2">
                                    <i class="fas fa-clock me-1"></i> <?= htmlspecialchars($pet['age']) ?>
                                </span>
                                <span
                                    class="badge <?= $pet['gender'] == 'Male' ? 'bg-info' : 'bg-danger' ?> text-white rounded-pill shadow-sm px-3 py-2">
                                    <i class="fas <?= $pet['gender'] == 'Male' ? 'fa-mars' : 'fa-venus' ?> me-1"></i>
                                    <?= htmlspecialchars($pet['gender']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($pet['pet_name']) ?></h4>
                            <p class="text-primary small fw-bold mb-3"><?= htmlspecialchars($pet['breed']) ?></p>

                            <hr class="opacity-10">

                            <div class="d-flex align-items-start text-muted mb-4">
                                <i class="fas fa-map-marker-alt text-danger me-2 mt-1"></i>
                                <div class="small">
                                    <span class="d-block fw-bold text-dark"><?= htmlspecialchars($pet['province']) ?></span>
                                    <span><?= htmlspecialchars($pet['address']) ?></span>
                                </div>
                            </div>

                            <a href="pet_details.php?id=<?= $pet['pet_id'] ?>"
                                class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                                View Full Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="py-5">
                    <i class="fas fa-paw fa-4x text-light mb-3"></i>
                    <h3 class="text-muted">No pets found matching your criteria.</h3>
                    <p class="text-secondary">Try changing your filters or searching for something else.</p>
                    <a href="explore.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Clear All Filters</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>
<script src="../../assets/js/adopter.js"></script>