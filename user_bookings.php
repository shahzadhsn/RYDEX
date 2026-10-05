<?php
$pageTitle = 'My Bookings';
$currentPage = 'bookings';
require_once __DIR__ . '/includes/user_header.php';
requireUserLogin();
require_once __DIR__ . '/includes/user_navbar.php';
require_once __DIR__ . '/config/database.php';

$user = getLoggedInUser();
$userId = $user['id'];
$db = getDB();

$myBookings = [];

if ($db) {
    try {
        $stmt = $db->prepare("
            SELECT b.*, 
                   CONCAT(v.brand, ' ', v.model) as vehicle_name, v.registration_number as vehicle_reg,
                   v.image as vehicle_image, v.category, v.price_per_day
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.customer_id = :cid
            ORDER BY b.id DESC
        ");
        $stmt->execute([':cid' => $userId]);
        $myBookings = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="container py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <span class="text-gold small font-cinzel">RESERVATIONS LEDGER</span>
            <h1 class="font-cinzel text-white mb-1">My Booking History</h1>
            <p class="text-muted small mb-0">Track active rentals, view payment receipts, and manage upcoming drives.</p>
        </div>

        <a href="user_fleet.php" class="gold-btn">
            <i class="bi bi-plus-lg me-1"></i> Book New Vehicle
        </a>
    </div>

    <!-- BOOKINGS TABLE CARD -->
    <div class="user-card p-4">
        <div class="table-responsive">
            <table class="recent-table w-100">
                <thead>
                    <tr>
                        <th>Booking Code</th>
                        <th>Vehicle Reserved</th>
                        <th>Pick-up Date</th>
                        <th>Return Date</th>
                        <th>Duration</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($myBookings)): ?>
                        <?php foreach ($myBookings as $bkg): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($bkg['booking_code']) ?></strong></td>
                                <td>
                                    <div class="fw-semibold text-white"><?= htmlspecialchars($bkg['vehicle_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($bkg['vehicle_reg']) ?></div>
                                </td>
                                <td><?= date('d M Y', strtotime($bkg['pickup_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($bkg['return_date'])) ?></td>
                                <td><?= intval($bkg['rental_days']) ?> Days</td>
                                <td><strong class="text-gold">₹<?= number_format($bkg['total_amount']) ?></strong></td>
                                <td>
                                    <span class="booking-status <?= strtolower($bkg['booking_status']) ?>">
                                        <?= htmlspecialchars($bkg['booking_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-light btn-sm" onclick="viewUserBooking(<?= $bkg['id'] ?>)">
                                            <i class="bi bi-eye me-1"></i> Receipt
                                        </button>
                                        <?php if ($bkg['booking_status'] !== 'Cancelled' && $bkg['booking_status'] !== 'Completed'): ?>
                                            <button class="btn btn-outline-danger btn-sm" onclick="cancelUserBooking(<?= $bkg['id'] ?>, '#<?= htmlspecialchars($bkg['booking_code']) ?>')">
                                                Cancel
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3 text-gold"></i>
                                <p class="mb-2">You have no active or historical bookings.</p>
                                <a href="user_fleet.php" class="gold-btn btn-sm text-decoration-none">Explore Fleet & Book Now</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- VIEW RECEIPT MODAL -->
<div class="modal fade" id="userReceiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="userReceiptTitle">Reservation Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="userReceiptBody">
        <!-- Dynamically loaded -->
      </div>
    </div>
  </div>
</div>

<script>
async function viewUserBooking(id) {
    try {
        const res = await fetch(`api/user_bookings.php?id=${id}`);
        const data = await res.json();
        if (!data.success || !data.data) {
            showRydexToast('Could not fetch receipt details.', 'error');
            return;
        }

        const bkg = data.data;
        document.getElementById('userReceiptTitle').textContent = `Reservation Receipt #${bkg.booking_code}`;

        document.getElementById('userReceiptBody').innerHTML = `
            <div class="row g-4">
                <div class="col-md-6 border-end border-secondary">
                    <h6 class="text-gold font-cinzel mb-3">Vehicle Details</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="${bkg.vehicle_image || 'images/bmw-m4.png'}" style="max-height: 80px; object-fit: contain;">
                        <div>
                            <strong class="d-block fs-5">${bkg.brand} ${bkg.model}</strong>
                            <span class="text-muted small">Reg: ${bkg.registration_number}</span>
                        </div>
                    </div>
                    <p class="mb-1 text-muted small">Category: <span class="vehicle-category">${bkg.category.toUpperCase()}</span></p>
                    <p class="mb-0 text-muted small">Daily Rate: <strong>₹${parseFloat(bkg.vehicle_price).toLocaleString('en-IN')} / day</strong></p>
                </div>

                <div class="col-md-6">
                    <h6 class="text-gold font-cinzel mb-3">Rental Summary</h6>
                    <p class="mb-1"><strong>Pick-up Date:</strong> ${bkg.pickup_date}</p>
                    <p class="mb-1"><strong>Return Date:</strong> ${bkg.return_date}</p>
                    <p class="mb-1"><strong>Total Duration:</strong> ${bkg.rental_days} Days</p>
                    <p class="mb-1"><strong>Payment Method:</strong> ${bkg.payment_method}</p>
                    <p class="mb-0"><strong>Booking Status:</strong> <span class="booking-status ${bkg.booking_status.toLowerCase()}">${bkg.booking_status}</span></p>
                </div>

                <div class="col-12 border-top border-secondary pt-3">
                    <div class="p-3 bg-dark border border-secondary rounded d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Payment Status</span>
                            <span class="badge bg-gold text-dark">${bkg.payment_status}</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block">Total Billed Amount</span>
                            <strong class="text-gold fs-3">₹${parseFloat(bkg.total_amount).toLocaleString('en-IN')}</strong>
                        </div>
                    </div>
                </div>

                ${bkg.notes ? `
                    <div class="col-12">
                        <span class="text-muted small d-block mb-1">Rental Notes</span>
                        <p class="text-light small bg-dark p-2 rounded border border-secondary">${bkg.notes}</p>
                    </div>
                ` : ''}
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('userReceiptModal'));
        modal.show();
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
    }
}

async function cancelUserBooking(id, code) {
    if (!confirm(`Are you sure you want to cancel reservation ${code}?`)) return;

    try {
        const res = await fetch('api/user_bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'cancel', id: id })
        });
        const data = await res.json();
        if (data.success) {
            showRydexToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showRydexToast(data.message || 'Error cancelling booking.', 'error');
        }
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
