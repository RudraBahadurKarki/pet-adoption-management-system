<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header("Location: /pet_adoption_system/login.php?error=Unauthorized Access");
    exit();
}


$admin_province = $_SESSION['province'] ?? '';
$is_super_admin = ($_SESSION['user_role'] == 'super_admin');

if (isset($_GET['approve_id'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['approve_id']);
    mysqli_query($conn, "UPDATE pets SET adoption_status = 'available' WHERE pet_id = '$pid'");
    header("Location: manage_all_pets.php?msg=Pet Approved Successfully");
    exit();
}

if (isset($_GET['set_pending_id'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['set_pending_id']);
    mysqli_query($conn, "UPDATE pets SET adoption_status = 'pending' WHERE pet_id = '$pid'");
    header("Location: manage_all_pets.php?msg=Pet moved back to Pending queue");
    exit();
}

if (isset($_GET['reject_id'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['reject_id']);
    mysqli_query($conn, "UPDATE pets SET adoption_status = 'rejected' WHERE pet_id = '$pid'");
    header("Location: manage_all_pets.php?msg=Pet Listing Rejected");
    exit();
}

if (isset($_GET['na_id'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['na_id']);
    mysqli_query($conn, "UPDATE pets SET adoption_status = 'not_applicable' WHERE pet_id = '$pid'");
    header("Location: manage_all_pets.php?msg=Status set to Not Applicable");
    exit();
}

if (isset($_GET['delete_id'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $pet_info = mysqli_query($conn, "SELECT pet_name FROM pets WHERE pet_id = '$pid'");
    $pet_data = mysqli_fetch_assoc($pet_info);
    $p_name = $pet_data['pet_name'] ?? 'Unknown Pet';

    $res = mysqli_query($conn, "SELECT image_path FROM pets WHERE pet_id = '$pid'");
    $pet = mysqli_fetch_assoc($res);
    if ($pet && !empty($pet['image_path'])) {
        $img = $pet['image_path'];
        $paths = ["../../uploads/$img", "../../assets/images/pets/$img"];
        foreach ($paths as $path) {
            if (file_exists($path))
                unlink($path);
        }
    }

    if (mysqli_query($conn, "DELETE FROM pets WHERE pet_id = '$pid'")) {
        $log_user_id = $_SESSION['user_id'];
        $log_desc = "Super Admin deleted pet: $p_name (ID: $pid)";
        mysqli_query($conn, "INSERT INTO audit_logs (user_id, action_type, description) VALUES ('$log_user_id', 'ADMIN_DELETE_PET', '$log_desc')");
    }
    header("Location: manage_all_pets.php?msg=Pet Permanently Deleted");
    exit();
}

function renderPetTable($result)
{
    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<div class="text-center py-5 border rounded-4 bg-white shadow-sm">
                <i class="fas fa-paw fa-3x text-secondary opacity-25 mb-3"></i>
                <h5 class="text-muted">No pet listings found.</h5>
              </div>';
        return;
    }

    echo '<div class="table-responsive">
            <table class="table table-hover align-middle border shadow-sm rounded-4 overflow-hidden bg-white">
                <thead class="table-dark text-uppercase small">
                    <tr>
                        <th class="ps-3">Pet Info</th>
                        <th>Breed & Details</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Adopter Details</th>
                    </tr>
                </thead>
                <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $aStatus = $row['adoption_status'];
        $fileName = $row['image_path'];
        $uploadPath = "../../uploads/" . $fileName;
        $assetsPath = "../../assets/images/pets/" . $fileName;
        $displayPath = (file_exists($uploadPath) && !empty($fileName)) ? $uploadPath :
            ((file_exists($assetsPath) && !empty($fileName)) ? $assetsPath : "../../assets/images/default-pet.png");

        $aColor = match ($aStatus) {
            'available' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'adopted' => 'primary',
            default => 'dark',
        };

        echo '<tr>
                <td class="ps-3">
                    <div class="d-flex align-items-center">
                        <img src="' . $displayPath . '" class="rounded-3 me-3 border" style="width:55px; height:55px; object-fit:cover;">
                        <div>
                            <div class="fw-bold text-dark">' . htmlspecialchars($row['pet_name']) . '</div>
                            <small class="text-muted text-capitalize">' . htmlspecialchars($row['species']) . '</small>
                            <div class="mt-1 pt-1 border-top" style="font-size: 0.7rem; line-height: 1.2;">
                                <span class="text-primary fw-bold"><i class="fas fa-store me-1"></i>' . htmlspecialchars($row['owner_name'] ?? 'System') . '</span><br>
                                <span class="text-muted">' . htmlspecialchars($row['owner_email'] ?? '') . '</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="small fw-bold">' . htmlspecialchars($row['breed'] ?? 'Mixed') . '</div>
                    <small class="text-muted">' . htmlspecialchars($row['gender'] ?? 'Unknown') . ' (' . htmlspecialchars($row['age'] ?? 'N/A') . ')</small>
                </td>
                <td>
                    <div class="small fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i>' . htmlspecialchars($row['province']) . '</div>
                    <small class="text-muted">' . htmlspecialchars($row['address'] ?? 'Nepal') . '</small>
                </td>
                <td>
                    <span class="badge bg-' . $aColor . '-subtle text-' . $aColor . ' border border-' . $aColor . ' rounded-pill px-3">
                        ' . str_replace('_', ' ', ucfirst($aStatus)) . '
                    </span>
                </td>
                <td>';

        if ($aStatus === 'adopted') {
            if (!empty($row['adopter_name'])) {
                echo '<div class="fw-bold small">' . htmlspecialchars($row['adopter_name']) . ' <span class="badge bg-info" style="font-size: 0.6rem;">' . htmlspecialchars($row['adopter_province']) . '</span></div>';
                echo '<div class="text-muted small mb-1"><i class="fas fa-phone-alt me-1"></i>' . htmlspecialchars($row['adopter_phone']) . '</div>';

                if (!empty($row['identity_doc'])) {
                    echo '<a href="../../assets/adopter_doc/' . htmlspecialchars($row['identity_doc']) . '" target="_blank" class="text-primary fw-bold" style="font-size: 10px; text-decoration: none;">
                            <i class="fas fa-file-alt me-1"></i>VIEW DOCUMENT
                          </a>';
                } else {
                    echo '<span class="text-muted small" style="font-size: 9px; font-style: italic;">No Doc Uploaded</span>';
                }
            } else {
                echo '<span class="text-danger small">Adopter Not Linked</span>';
            }
        } else {
            echo '<span class="text-muted small italic">Not adopted</span>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table></div>';
}

$base_query = "SELECT p.*,
                      u.full_name as owner_name,
                      u.email as owner_email,
                      u.phone as owner_phone,
                      u.province as province,
                      u.address as address,
                      adopter.full_name as adopter_name,
                      adopter.phone as adopter_phone,
                      adopter.province as adopter_province,
                      aa.identity_doc
               FROM pets p
               INNER JOIN users u ON p.current_owner_id = u.user_id
               LEFT JOIN users adopter ON p.final_adopter_id = adopter.user_id
               LEFT JOIN adoption_applications aa ON p.pet_id = aa.target_pet_id
                    AND p.final_adopter_id = aa.applicant_id
                    AND aa.app_status = 'approved'";

$where_clause = " WHERE 1=1";

if (!$is_super_admin && !empty($admin_province)) {
    $safe_province = mysqli_real_escape_string($conn, $admin_province);
    $where_clause .= " AND u.province = '$safe_province'";
}

$all_pets = mysqli_query($conn, $base_query . $where_clause . " ORDER BY p.pet_id DESC");

include '../../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-paw text-primary me-2"></i>Pet Database</h2>
            <p class="text-muted small">Location:
                <strong><?= $is_super_admin ? 'All Regions' : htmlspecialchars($admin_province) ?></strong>
            </p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-dark rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="auto-close-alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
        <?php renderPetTable($all_pets); ?>
    </div>
</div>




<?php include '../../includes/footer.php'; ?>


<script src="../../assets/js/admin.js"></script>