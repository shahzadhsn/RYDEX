<?php
$pageTitle = 'Account Settings';
$currentPage = 'profile';
require_once __DIR__ . '/includes/user_header.php';
requireUserLogin();
require_once __DIR__ . '/includes/user_navbar.php';
require_once __DIR__ . '/config/database.php';

$user = getLoggedInUser();
$userId = $user['id'];
$db = getDB();

$custData = $user;
if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $fetched = $stmt->fetch();
        if ($fetched) $custData = array_merge($custData, $fetched);
    } catch (Exception $e) {}
}
?>

<div class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <span class="text-gold small font-cinzel">CLIENT VERIFICATION & PROFILE</span>
        <h1 class="font-cinzel text-white mb-1">Account & License Settings</h1>
        <p class="text-muted small">Update your personal profile, phone number, residential address, and driving permit information.</p>
    </div>

    <div class="row g-4">
        <!-- Profile Details Form -->
        <div class="col-lg-7">
            <div class="user-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <h5 class="font-cinzel text-gold mb-0"><i class="bi bi-person me-2"></i>Personal Profile</h5>
                    <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($custData['customer_code'] ?? 'RYX-C101') ?></span>
                </div>

                <form id="userProfileForm" onsubmit="handleSaveProfile(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Full Name *</label>
                            <input type="text" id="profName" class="form-control rydex-input" value="<?= htmlspecialchars($custData['full_name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email Address (Read Only)</label>
                            <input type="email" class="form-control rydex-input bg-dark" value="<?= htmlspecialchars($custData['email'] ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Phone Number *</label>
                            <input type="text" id="profPhone" class="form-control rydex-input" value="<?= htmlspecialchars($custData['phone'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Driving License Number *</label>
                            <input type="text" id="profLicense" class="form-control rydex-input" value="<?= htmlspecialchars($custData['license_number'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">City</label>
                            <input type="text" id="profCity" class="form-control rydex-input" value="<?= htmlspecialchars($custData['city'] ?? 'Mumbai') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">State</label>
                            <input type="text" id="profState" class="form-control rydex-input" value="<?= htmlspecialchars($custData['state'] ?? 'Maharashtra') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted small">Residential Address</label>
                            <textarea id="profAddress" class="form-control rydex-input" rows="2"><?= htmlspecialchars($custData['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="gold-btn mt-4">
                        Save Profile Updates
                    </button>
                </form>
            </div>
        </div>

        <!-- Security & Password Form -->
        <div class="col-lg-5">
            <div class="user-card p-4 h-100">
                <div class="mb-4 border-bottom border-secondary pb-3">
                    <h5 class="font-cinzel text-gold mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                </div>

                <form id="userPassForm" onsubmit="handleUserChangePass(event)">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Password *</label>
                        <input type="password" id="profCurrPass" class="form-control rydex-input" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">New Password *</label>
                        <input type="password" id="profNewPass" class="form-control rydex-input" placeholder="At least 6 characters" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Confirm New Password *</label>
                        <input type="password" id="profConfirmPass" class="form-control rydex-input" required>
                    </div>

                    <button type="submit" class="gold-btn mt-3 w-100">
                        Update Account Password
                    </button>
                </form>

                <div class="p-3 bg-dark border border-secondary rounded mt-4">
                    <span class="text-gold font-cinzel small fw-bold d-block mb-1"><i class="bi bi-info-circle me-1"></i> Account Security</span>
                    <p class="text-muted small mb-0">Your identity and driving permit details are encrypted. Only verified active accounts can reserve luxury vehicles.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
async function handleSaveProfile(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;

    const data = {
        action: 'update_profile',
        full_name: document.getElementById('profName').value.trim(),
        phone: document.getElementById('profPhone').value.trim(),
        license_number: document.getElementById('profLicense').value.trim(),
        city: document.getElementById('profCity').value.trim(),
        state: document.getElementById('profState').value.trim(),
        address: document.getElementById('profAddress').value.trim()
    };

    try {
        const res = await fetch('api/user_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const resp = await res.json();
        if (resp.success) {
            showRydexToast(resp.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showRydexToast(resp.message || 'Error updating profile.', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showRydexToast('Network error.', 'error');
        btn.disabled = false;
    }
}

async function handleUserChangePass(e) {
    e.preventDefault();
    const curr = document.getElementById('profCurrPass').value;
    const newP = document.getElementById('profNewPass').value;
    const conf = document.getElementById('profConfirmPass').value;

    if (newP !== conf) {
        showRydexToast('New password and confirmation do not match.', 'error');
        return;
    }

    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;

    try {
        const res = await fetch('api/user_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'change_password',
                current_password: curr,
                new_password: newP
            })
        });
        const resp = await res.json();
        if (resp.success) {
            showRydexToast(resp.message, 'success');
            e.target.reset();
            btn.disabled = false;
        } else {
            showRydexToast(resp.message || 'Error updating password.', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showRydexToast('Network error.', 'error');
        btn.disabled = false;
    }
}
</script>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
