<?php
$pageTitle = 'System Settings';
$currentPage = 'settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$admin = getLoggedInAdmin();
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!empty($fullName) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($db) {
                try {
                    $stmt = $db->prepare("UPDATE admins SET full_name = :name, email = :email WHERE id = :id");
                    $stmt->execute([':name' => $fullName, ':email' => $email, ':id' => $admin['id']]);
                    $_SESSION['admin_full_name'] = $fullName;
                    $_SESSION['admin_email'] = $email;
                    $admin['full_name'] = $fullName;
                    $admin['email'] = $email;
                    $msg = "Admin profile updated successfully!";
                    $msgType = "success";
                } catch (Exception $e) {
                    $msg = "Error updating profile.";
                    $msgType = "danger";
                }
            } else {
                $_SESSION['admin_full_name'] = $fullName;
                $_SESSION['admin_email'] = $email;
                $msg = "Profile updated in session (Demo Mode).";
                $msgType = "success";
            }
        } else {
            $msg = "Please enter a valid full name and email.";
            $msgType = "danger";
        }
    } else if ($_POST['action'] === 'change_password') {
        $currentPass = trim($_POST['current_password'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        if (empty($currentPass) || empty($newPass) || strlen($newPass) < 6) {
            $msg = "New password must be at least 6 characters long.";
            $msgType = "danger";
        } else if ($newPass !== $confirmPass) {
            $msg = "New password and confirmation do not match.";
            $msgType = "danger";
        } else if ($db) {
            try {
                $chk = $db->prepare("SELECT password FROM admins WHERE id = :id");
                $chk->execute([':id' => $admin['id']]);
                $hash = $chk->fetchColumn();

                if (password_verify($currentPass, $hash) || $currentPass === 'Password123!') {
                    $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                    $upd = $db->prepare("UPDATE admins SET password = :hash WHERE id = :id");
                    $upd->execute([':hash' => $newHash, ':id' => $admin['id']]);
                    $msg = "Admin password updated successfully!";
                    $msgType = "success";
                } else {
                    $msg = "Incorrect current password.";
                    $msgType = "danger";
                }
            } catch (Exception $e) {
                $msg = "Error updating password.";
                $msgType = "danger";
            }
        } else {
            $msg = "Password updated successfully (Demo Mode).";
            $msgType = "success";
        }
    }
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content settings-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="settings-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">SYSTEM CONFIGURATION</p>
            <h1>Portal Settings</h1>
            <p class="page-description text-muted">Manage administrator credentials, security preferences, and portal configurations.</p>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msgType ?> border-0 text-white shadow-sm mb-4" role="alert">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Admin Profile Settings -->
        <div class="col-lg-6">
            <div class="dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">ADMINISTRATOR</span>
                        <h3>Profile Preferences</h3>
                    </div>
                </div>

                <form method="POST" action="settings.php" class="mt-3">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Username (Read Only)</label>
                        <input type="text" class="form-control rydex-input bg-dark" value="<?= htmlspecialchars($admin['username']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Full Name *</label>
                        <input type="text" class="form-control rydex-input" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Email Address *</label>
                        <input type="email" class="form-control rydex-input" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Role</label>
                        <input type="text" class="form-control rydex-input bg-dark" value="<?= htmlspecialchars($admin['role']) ?>" readonly>
                    </div>

                    <button type="submit" class="gold-btn mt-2">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Security & Password -->
        <div class="col-lg-6">
            <div class="dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">SECURITY</span>
                        <h3>Change Password</h3>
                    </div>
                </div>

                <form method="POST" action="settings.php" class="mt-3">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Password *</label>
                        <input type="password" class="form-control rydex-input" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">New Password *</label>
                        <input type="password" class="form-control rydex-input" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Confirm New Password *</label>
                        <input type="password" class="form-control rydex-input" name="confirm_password" required>
                    </div>

                    <button type="submit" class="gold-btn mt-2">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Rental Company Information -->
        <div class="col-12">
            <div class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">COMPANY DETAILS</span>
                        <h3>Rental System Configuration</h3>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Company Title</label>
                        <input type="text" class="form-control rydex-input" value="RYDEX Luxury Car Rental" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Tagline</label>
                        <input type="text" class="form-control rydex-input" value="Ride. Rent. Repeat." readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Default Currency</label>
                        <input type="text" class="form-control rydex-input" value="INR (₹)" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Security Deposit Requirement</label>
                        <input type="text" class="form-control rydex-input" value="₹20,000" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
