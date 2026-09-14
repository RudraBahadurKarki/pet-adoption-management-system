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
$admin_id = $_SESSION['user_id'];

function renderUserTable($result, $nameLabel, $isShelter = false)
{
    echo '<div class="table-responsive">
            <table class="table table-hover align-middle table-sm" style="table-layout: auto;">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 20%;">' . $nameLabel . '</th>
                        <th style="width: 30%;">Contact Info</th>
                        <th style="width: 25%;">' . ($isShelter ? 'Docs' : 'Adopted Pets') . '</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>';

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $currentStatus = $row['status'] ?? 'pending';
            $statusClass = ($currentStatus == 'active')
                ? 'bg-success-subtle text-success border-success'
                : 'bg-warning-subtle text-warning border-warning';

            $btnLabel = ($currentStatus == 'active') ? 'Deactivate' : 'Activate';
            $btnClass = ($currentStatus == 'active') ? 'btn-outline-danger' : 'btn-success text-white';

            echo '<tr>
                    <td class="py-3">
                        <div class="fw-bold text-dark lh-1">' . htmlspecialchars($row['full_name']) . '</div>
                        <div class="text-muted" style="font-size: 0.75rem;">' . htmlspecialchars($row['province'] ?? 'Unknown Province') . '</div>
                        <small class="text-muted" style="font-size: 0.75rem;">Joined ' . date("M Y", strtotime($row['created_at'])) . '</small>
                    </td>
                    <td>
                        <div class="small text-truncate" style="max-width: 200px;">' . htmlspecialchars($row['email']) . '</div>
                        <small class="text-muted">' . htmlspecialchars($row['phone'] ?? 'N/A') . '</small>
                    </td>';

            if ($isShelter) {
                echo '<td>';
                if (!empty($row['verification_doc'])) {
                    $doc_name = $row['verification_doc'];
                    $url_path = "/pet_adoption_system/assets/shelterdocs/verification/" . htmlspecialchars($doc_name);
                    echo '<a href="' . $url_path . '" target="_blank" class="btn btn-sm btn-light border py-1 px-2 shadow-sm" style="font-size: 0.90rem;">
                            <i class="fas fa-file text-danger"></i> View
                          </a>';
                } else {
                    echo '<span class="text-muted small">None</span>';
                }
                echo '</td>';
            } else {
                echo '<td>';
                if (!empty($row['adopted_pets'])) {
                    $pets = explode(', ', $row['adopted_pets']);
                    echo '<div class="d-flex flex-column gap-1">';
                    foreach ($pets as $pet) {
                        echo '<div><span class="badge bg-info-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.9rem; padding: 0.2rem 0.5rem;">
                                <i class="fas fa-paw"></i> ' . htmlspecialchars($pet) . '
                              </span></div>';
                    }
                    echo '</div>';
                } else {
                    echo '<span class="text-muted small">None</span>';
                }
                echo '</td>';
            }

            echo '<td>
                        <span class="badge ' . $statusClass . ' border rounded-pill py-1 px-2" style="font-size: 0.85rem;">
                            ' . ucfirst($currentStatus) . '
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="manage_users.php?toggle_id=' . $row['user_id'] . '&current=' . $currentStatus . '"
                           class="btn btn-sm ' . $btnClass . ' rounded-pill py-1 px-3 shadow-sm" style="font-size: 0.75rem; min-width: 90px;">
                           ' . $btnLabel . '
                        </a>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="5" class="text-center py-5 text-muted">No users found.</td></tr>';
    }
    echo '</tbody></table></div>';
}
// ACTION LOGIC
if (isset($_GET['toggle_id'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['toggle_id']);
    $current = $_GET['current'];
    $new_status = ($current == 'active') ? 'pending' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $uid);

    if ($stmt->execute()) {
        $log_desc = "Admin #$admin_id changed User #$uid status from $current to $new_status";
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, description) VALUES (?, 'USER_TOGGLE', ?)");
        $log_stmt->bind_param("is", $admin_id, $log_desc);
        $log_stmt->execute();

        header("Location: manage_users.php?msg=User status updated successfully");
    } else {
        header("Location: manage_users.php?msg=Error Updating Status");
    }
    exit();
}

// --- DATA FETCHING ---
$shelter_query = "SELECT * FROM users WHERE role = 'shelter'";
if (!empty($admin_province)) {
    $shelter_query .= " AND province = '" . mysqli_real_escape_string($conn, $admin_province) . "'";
}
$shelter_query .= " ORDER BY user_id DESC";
$shelters = mysqli_query($conn, $shelter_query);

// Corrected subquery: Joining pets with users to get the shelter's province
$adopter_query = "SELECT u.*,
                 (SELECT GROUP_CONCAT(CONCAT(p.pet_name, ' (', s.province, ')') SEPARATOR ', ')
                  FROM adoption_applications aa
                  JOIN pets p ON aa.target_pet_id = p.pet_id
                  JOIN users s ON p.current_owner_id = s.user_id
                  WHERE aa.applicant_id = u.user_id AND aa.app_status = 'approved') AS adopted_pets
                  FROM users u
                  WHERE u.role = 'adopter'";

if (!empty($admin_province)) {
    $adopter_query .= " AND u.province = '" . mysqli_real_escape_string($conn, $admin_province) . "'";
}
$adopter_query .= " GROUP BY u.user_id ORDER BY u.user_id DESC";
$adopters = mysqli_query($conn, $adopter_query);

include '../../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1"><i class="fas fa-users-cog me-2"></i>User Management</h2>
            <p class="text-muted small">Managing: <strong><?= $admin_province ?: 'All Regions' ?></strong></p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 alert-dismissible fade show" id="auto-close-alert">
            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-pills mb-4 bg-light p-2 rounded-4" id="userTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill px-4" data-bs-toggle="pill"
                data-bs-target="#tab-shelters">Shelters</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4" data-bs-toggle="pill"
                data-bs-target="#tab-adopters">Adopters</button>
        </li>
    </ul>

    <div class="tab-content shadow-sm rounded-4 bg-white p-4 border">
        <div class="tab-pane fade show active" id="tab-shelters">
            <?php renderUserTable($shelters, 'Shelter', true); ?>
        </div>
        <div class="tab-pane fade" id="tab-adopters">
            <?php renderUserTable($adopters, 'Adopter Name', false); ?>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>


<script src="../../assets/js/admin.js"></script>
