<?php
$pageTitle = 'License Verification Queue';
$currentPage = 'license';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$customers = [];
$vStats = ['pending' => 0, 'verified' => 0, 'rejected' => 0];

if ($db) {
    try {
        $customers = $db->query("
            SELECT id, customer_code, full_name, email, phone, license_number, license_expiry,
                   COALESCE(license_front_image, 'images/license_sample.jpg') as license_front_image,
                   COALESCE(license_back_image, 'images/license_sample.jpg') as license_back_image,
                   COALESCE(verification_status, 'Verified') as verification_status,
                   COALESCE(verification_notes, '') as verification_notes,
                   verified_at, created_at
            FROM customers
            ORDER BY CASE WHEN verification_status = 'Pending' THEN 1 WHEN verification_status = 'Rejected' THEN 2 ELSE 3 END, id DESC
        ")->fetchAll();

        foreach ($customers as $c) {
            $st = strtolower($c['verification_status']);
            if (isset($vStats[$st])) $vStats[$st]++;
        }
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content license-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <p class="dashboard-greeting">SECURITY & COMPLIANCE</p>
            <h1>License Verification Queue</h1>
            <p class="page-description text-muted">Verify driving permits, inspect document photos, and approve client rental privileges.</p>
        </div>
    </div>

    <!-- VERIFICATION STAT CARDS -->
    <section class="booking-stats mb-4">
        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-clock-history text-warning"></i></div>
            <div>
                <span>Pending Verification</span>
                <strong class="text-warning"><?= intval($vStats['pending']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
            <div>
                <span>Verified Clients</span>
                <strong class="text-success"><?= intval($vStats['verified']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-x-circle-fill text-danger"></i></div>
            <div>
                <span>Rejected Documents</span>
                <strong class="text-danger"><?= intval($vStats['rejected']) ?></strong>
            </div>
        </div>
    </section>

    <!-- SEARCH & FILTER TOOLBAR -->
    <section class="booking-toolbar mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="booking-search flex-grow-1">
            <i class="bi bi-search"></i>
            <input type="text" id="licenseSearch" placeholder="Search customer name, email, phone or license number..." onkeyup="filterLicenseTable()">
        </div>

        <select class="booking-filter" id="licenseStatusFilter" onchange="filterLicenseTable()">
            <option value="all">All Verification Statuses</option>
            <option value="Pending">Pending Review</option>
            <option value="Verified">Verified</option>
            <option value="Rejected">Rejected</option>
        </select>
    </section>

    <!-- VERIFICATION TABLE -->
    <section class="dashboard-panel p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="recent-table w-100" id="licenseTable">
                <thead>
                    <tr>
                        <th>Client Code</th>
                        <th>Client Name</th>
                        <th>Driving License</th>
                        <th>Document Photos</th>
                        <th>Verification Status</th>
                        <th>Notes / Logs</th>
                        <th class="text-end">Verification Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $c): ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($c['full_name'] . ' ' . $c['email'] . ' ' . $c['phone'] . ' ' . $c['license_number'] . ' ' . $c['customer_code'])) ?>"
                                data-status="<?= htmlspecialchars($c['verification_status']) ?>">
                                <td><strong>#<?= htmlspecialchars($c['customer_code']) ?></strong></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($c['email']) ?></div>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($c['license_number']) ?></strong></div>
                                    <div class="text-muted small">Exp: <?= date('d M Y', strtotime($c['license_expiry'])) ?></div>
                                </td>
                                <td>
                                    <button class="btn btn-outline-gold btn-sm py-1 px-2" onclick="inspectLicenseDocs(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['full_name'])) ?>', '<?= htmlspecialchars($c['license_front_image']) ?>', '<?= htmlspecialchars($c['license_back_image']) ?>')">
                                        <i class="bi bi-image me-1"></i> Inspect Photos
                                    </button>
                                </td>
                                <td>
                                    <?php if ($c['verification_status'] === 'Verified'): ?>
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-patch-check-fill me-1"></i> Verified</span>
                                    <?php elseif ($c['verification_status'] === 'Pending'): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> Pending Review</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-octagon me-1"></i> Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($c['verification_notes'] ?: 'No notes') ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success btn-sm" onclick="setLicenseStatus(<?= $c['id'] ?>, 'Verified')" title="Approve License">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="setLicenseStatus(<?= $c['id'] ?>, 'Rejected')" title="Reject License">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No license verification records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<!-- DOCUMENT INSPECTION MODAL -->
<div class="modal fade" id="docInspectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="docInspectTitle">Driving License Document Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <div class="row g-3">
            <div class="col-md-6">
                <span class="text-muted small d-block mb-2">License Front Photo</span>
                <img id="docFrontImg" src="" class="img-fluid rounded border border-secondary" style="max-height: 250px; object-fit: contain;">
            </div>
            <div class="col-md-6">
                <span class="text-muted small d-block mb-2">License Back Photo</span>
                <img id="docBackImg" src="" class="img-fluid rounded border border-secondary" style="max-height: 250px; object-fit: contain;">
            </div>
        </div>
      </div>
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function filterLicenseTable() {
    const search = document.getElementById('licenseSearch').value.toLowerCase().trim();
    const status = document.getElementById('licenseStatusFilter').value;
    const rows = document.querySelectorAll('#licenseTable tbody tr');

    rows.forEach(row => {
        const sMatch = !search || (row.getAttribute('data-search') || '').includes(search);
        const stMatch = status === 'all' || row.getAttribute('data-status') === status;
        row.style.display = (sMatch && stMatch) ? '' : 'none';
    });
}

function inspectLicenseDocs(id, name, front, back) {
    document.getElementById('docInspectTitle').textContent = `Document Photos: ${name}`;
    document.getElementById('docFrontImg').src = front || 'images/license_sample.jpg';
    document.getElementById('docBackImg').src = back || 'images/license_sample.jpg';

    const modal = new bootstrap.Modal(document.getElementById('docInspectModal'));
    modal.show();
}

async function setLicenseStatus(id, newStatus) {
    let notes = '';
    if (newStatus === 'Rejected') {
        notes = prompt('Please enter rejection reason (e.g. "License photo blurry, please re-upload clear photo"):', 'Document photo unreadable, please re-upload.');
        if (notes === null) return;
    } else {
        notes = 'License verified by Admin Alex Hunter.';
    }

    try {
        const res = await fetch('api/license_verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_status', customer_id: id, status: newStatus, notes: notes })
        });
        const data = await res.json();
        if (data.success) {
            showRydexToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showRydexToast(data.message || 'Error updating status.', 'error');
        }
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
