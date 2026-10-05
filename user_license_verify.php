<?php
$pageTitle = 'License Verification';
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

$vStatus = $custData['verification_status'] ?? 'Verified';
?>

<div class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <span class="text-gold small font-cinzel">SECURITY & COMPLIANCE</span>
        <h1 class="font-cinzel text-white mb-1">Driving License Verification Portal</h1>
        <p class="text-muted small">Upload your government driving permit to unlock instant high-performance vehicle reservations.</p>
    </div>

    <!-- STATUS BANNER -->
    <div class="user-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <?php if ($vStatus === 'Verified'): ?>
                    <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-patch-check-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="text-success mb-1">License Verified & Cleared</h4>
                        <p class="text-muted small mb-0">Your driving permit is active. You have full access to reserve all flagship supercars.</p>
                    </div>
                <?php elseif ($vStatus === 'Pending'): ?>
                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <div>
                        <h4 class="text-warning mb-1">Verification Pending Admin Scan</h4>
                        <p class="text-muted small mb-0">Your license documents have been submitted. Our compliance team is verifying your details.</p>
                    </div>
                <?php elseif ($vStatus === 'Rejected'): ?>
                    <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-x-octagon-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="text-danger mb-1">Verification Rejected</h4>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($custData['verification_notes'] ?: 'Please upload a clear, un-cropped photo of your driving license.') ?></p>
                    </div>
                <?php else: ?>
                    <div class="rounded-circle bg-secondary text-white p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <h4 class="text-gold mb-1">Upload Driving Permit</h4>
                        <p class="text-muted small mb-0">Submit your front and back license photos to complete account verification.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <span class="badge bg-dark border border-secondary px-3 py-2 fs-6">
                    Status: <strong class="text-gold"><?= htmlspecialchars($vStatus) ?></strong>
                </span>
            </div>
        </div>
    </div>

    <!-- VERIFICATION FORM CARD -->
    <div class="user-card p-4">
        <h5 class="font-cinzel text-gold mb-4 border-bottom border-secondary pb-3">Submit Permit Documents</h5>

        <form id="licenseVerifyForm" onsubmit="handleUploadLicense(event)">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Driving License Number *</label>
                    <input type="text" id="vLicNo" class="form-control rydex-input" value="<?= htmlspecialchars($custData['license_number'] ?? '') ?>" placeholder="e.g. DL-MH02-2022-9988" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">License Expiry Date *</label>
                    <input type="date" id="vLicExp" class="form-control rydex-input" value="<?= htmlspecialchars($custData['license_expiry'] ?? '2029-12-31') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">License Front Image URL / Path *</label>
                    <input type="text" id="vLicFront" class="form-control rydex-input" value="<?= htmlspecialchars($custData['license_front_image'] ?? 'images/license_sample.jpg') ?>" required>
                    <span class="text-muted extra-small" style="font-size: 11px;">Upload front photo of your government driving card.</span>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">License Back Image URL / Path *</label>
                    <input type="text" id="vLicBack" class="form-control rydex-input" value="<?= htmlspecialchars($custData['license_back_image'] ?? 'images/license_sample.jpg') ?>" required>
                    <span class="text-muted extra-small" style="font-size: 11px;">Upload back side showing endorsement details.</span>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="gold-btn py-3 px-4">
                        <i class="bi bi-upload me-2"></i> Submit Documents for Verification
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
async function handleUploadLicense(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;

    const data = {
        action: 'submit_verification',
        license_number: document.getElementById('vLicNo').value.trim(),
        license_expiry: document.getElementById('vLicExp').value.trim(),
        license_front_image: document.getElementById('vLicFront').value.trim(),
        license_back_image: document.getElementById('vLicBack').value.trim()
    };

    try {
        const res = await fetch('api/license_verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const resp = await res.json();
        if (resp.success) {
            showRydexToast(resp.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showRydexToast(resp.message || 'Error submitting license.', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
        btn.disabled = false;
    }
}
</script>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
