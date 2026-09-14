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
$message = "";

if (isset($_POST['add_pet_btn'])) {
    $name = trim(mysqli_real_escape_string($conn, $_POST['pet_name']));
    $species = trim(mysqli_real_escape_string($conn, $_POST['species'] ?? ''));
    $breed = trim(mysqli_real_escape_string($conn, $_POST['breed']));
    $age = trim(mysqli_real_escape_string($conn, $_POST['age']));
    $gender = trim(mysqli_real_escape_string($conn, $_POST['gender'] ?? ''));
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));

    $image_uploaded = !empty($_FILES['pet_image']['name']);

    if (empty($name) || empty($species) || empty($breed) || empty($age) || empty($gender) || empty($description) || !$image_uploaded) {
        $message = "
        <div class='alert alert-danger rounded-4 border-0 shadow-sm'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Error:</strong> All fields, including the pet photo, are strictly required.
        </div>";
    } else {
        $original_name = basename($_FILES['pet_image']['name']);
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            $message = "<div class='alert alert-danger rounded-4 border-0 shadow-sm'>Invalid file type. Please upload JPG, PNG, or WEBP.</div>";
        } else {
            $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $target_dir = "../../assets/images/pets/";
            $target_file = $target_dir . $image_name;

            $query = "INSERT INTO pets (pet_name, species, breed, age, gender, description, image_path, current_owner_id, adoption_status)
                      VALUES ('$name', '$species', '$breed', '$age', '$gender', '$description', '$image_name', '$user_id', 'available')";

            if (mysqli_query($conn, $query)) {
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
                    $log_desc = "Shelter (ID: $user_id) listed a new pet: $name";
                    mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description) VALUES ('$user_id', 'SHELTER_ADD_PET', '$log_desc')");

                    $_SESSION['status_msg'] = "Pet listing published successfully!";

                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $message = "<div class='alert alert-danger rounded-4 border-0 shadow-sm'>Pet data saved, but image upload failed. Check folder permissions.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger rounded-4 border-0 shadow-sm'>Database Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

if (isset($_SESSION['status_msg'])) {
    $message = "<div class='alert alert-success rounded-4 border-0 shadow-sm' id='auto-close-alert'><i class='fas fa-check-circle me-2'></i> " . $_SESSION['status_msg'] . "</div>";
    unset($_SESSION['status_msg']);
}
include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/shelter.css">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <?= $message ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-success text-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-paw me-2"></i> List a Pet for Adoption</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="add_pet.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Pet Name</label>
                                <input type="text" name="pet_name" class="form-control rounded-pill px-3"
                                    placeholder="e.g. Brusky" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Species</label>
                                <select name="species" class="form-select rounded-pill px-3" required>
                                    <option value="" selected disabled>Select Species</option>
                                    <option value="dog">Dog</option>
                                    <option value="cat">Cat</option>
                                    <option value="bird">Bird</option>
                                    <option value="others">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Breed</label>
                                <input type="text" name="breed" class="form-control rounded-pill px-3"
                                    placeholder="e.g. Husky" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Age</label>
                                <input type="text" name="age" class="form-control rounded-pill px-3"
                                    placeholder="e.g. 2 Years" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Gender</label>
                                <select name="gender" class="form-select rounded-pill px-3" required>
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Description</label>
                            <textarea name="description" class="form-control rounded-4 px-3" rows="4"
                                placeholder="Personality, health, vaccinated?..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Pet Photo</label>
                            <input type="file" name="pet_image" class="form-control rounded-pill" accept="image/*"
                                required>
                            <div class="mt-2">
                                <small class="text-info d-block"><i class="fas fa-info-circle me-1"></i> Address is
                                    automatically set to your shelter location.</small>
                                <small class="text-danger d-block"><i class="fas fa-exclamation-circle me-1"></i> All
                                    details will go live immediately after publishing.</small>
                            </div>
                        </div>

                        <hr class="text-muted mb-4">

                        <div class="d-flex gap-2">
                            <button type="submit" name="add_pet_btn"
                                class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                Publish Pet Listing
                            </button>
                            <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>


<script src="../../assets/js/shelter.js"></script>