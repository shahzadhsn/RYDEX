<?php
$pageTitle = 'My Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/includes/user_header.php';
requireUserLogin();
require_once __DIR__ . '/includes/user_navbar.php';
require_once __DIR__ . '/config/database.php';

$user = getLoggedInUser();
$userId = $user['id'];
$db = getDB();

$myBookings = [];
$activeBooking = null;
$totalTrips = 0;
$totalSpent = 0;

if ($db) {
    try {
        $stmt = $db->prepare("
            SELECT b.*, 
                   CONCAT(v.brand, ' ', v.model) as vehicle_name, v.registration_number as vehicle_reg,
                   v.image as vehicle_image, v.category
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.customer_id = :cid
            ORDER BY b.id DESC
        ");
        $stmt->execute([':cid' => $userId]);
        $myBookings = $stmt->fetchAll();

        foreach ($myBookings as $bkg) {
            if ($bkg['booking_status'] === 'Confirmed' && !$activeBooking) {
                $activeBooking = $bkg;
            }
            if ($bkg['booking_status'] !== 'Cancelled') {
                $totalTrips++;
                $totalSpent += floatval($bkg['total_amount']);
            }
        }
    } catch (Exception $e) {}
}
?>

<div class="container py-4">

    <!-- WELCOME BANNER -->
    <div class="p-4 p-md-5 mb-4 rounded user-card border-gold position-relative overflow-hidden">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-gold text-dark mb-2 px-3 py-1 font-cinzel">CLIENT CODE: <?= htmlspecialchars($user['customer_code']) ?></span>
                <h1 class="font-cinzel text-white display-6 mb-2">Welcome Back, <?= htmlspecialchars($user['full_name']) ?></h1>
                <p class="text-muted mb-4">Manage your luxury supercar rentals, view active reservations, or discover our flagship vehicles.</p>
                
                <div class="d-flex gap-3 flex-wrap">
                    <a href="user_fleet.php" class="gold-btn">
                        <i class="bi bi-car-front me-1"></i> Book a Luxury Car
                    </a>
                    <a href="user_bookings.php" class="outline-btn">
                        <i class="bi bi-journal-text me-1"></i> My Reservations
                    </a>
                </div>
            </div>

            <div class="col-lg-4 text-center d-none d-lg-block">
                <img src="images/bmw-m4.png" class="img-fluid drop-shadow" style="max-height: 160px; object-fit: contain;" alt="RYDEX Luxury">
            </div>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="user-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">ACTIVE RESERVATION</span>
                    <i class="bi bi-key-fill text-gold fs-4"></i>
                </div>
                <h3 class="mb-1 text-white"><?= $activeBooking ? '1 Active' : 'None' ?></h3>
                <span class="text-muted small"><?= $activeBooking ? htmlspecialchars($activeBooking['vehicle_name']) : 'No current drive' ?></span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="user-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">TOTAL TRIPS COMPLETED</span>
                    <i class="bi bi-trophy-fill text-gold fs-4"></i>
                </div>
                <h3 class="mb-1 text-white"><?= intval($totalTrips) ?></h3>
                <span class="text-muted small">Total Bookings Logged</span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="user-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">TOTAL SPENT WITH RYDEX</span>
                    <i class="bi bi-wallet2 text-gold fs-4"></i>
                </div>
                <h3 class="mb-1 text-gold">₹<?= number_format($totalSpent) ?></h3>
                <span class="text-muted small">Lifetime Luxury Spend</span>
            </div>
        </div>
    </div>

    <!-- ACTIVE BOOKING BANNER -->
    <?php if ($activeBooking): ?>
        <div class="user-card p-4 mb-4 border border-warning">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 border-bottom border-secondary pb-3">
                <div>
                    <span class="badge bg-success mb-1">LIVE CONFIRMED RENTAL</span>
                    <h4 class="font-cinzel text-gold mb-0">Reservation #<?= htmlspecialchars($activeBooking['booking_code']) ?></h4>
                </div>
                <button class="gold-btn btn-sm" onclick="viewBooking(<?= $activeBooking['id'] ?>)">View Receipt & Specs</button>
            </div>

            <div class="row align-items-center g-3">
                <div class="col-md-3 text-center">
                    <img src="<?= htmlspecialchars($activeBooking['vehicle_image']) ?>" class="img-fluid" style="max-height: 100px; object-fit: contain;">
                </div>
                <div class="col-md-5">
                    <h5><?= htmlspecialchars($activeBooking['vehicle_name']) ?></h5>
                    <p class="text-muted small mb-1">Registration: <strong><?= htmlspecialchars($activeBooking['vehicle_reg']) ?></strong></p>
                    <p class="text-muted small mb-0">Category: <span class="vehicle-category"><?= htmlspecialchars(strtoupper($activeBooking['category'])) ?></span></p>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-dark border border-secondary rounded">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Pick-up:</span>
                            <strong class="text-white"><?= date('d M Y', strtotime($activeBooking['pickup_date'])) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-1">
                            <span>Return:</span>
                            <strong class="text-white"><?= date('d M Y', strtotime($activeBooking['return_date'])) ?></strong>
                        </div>
                        <hr class="my-2 border-secondary">
                        <div class="d-flex justify-content-between text-gold">
                            <span>Total Amount:</span>
                            <strong>₹<?= number_format($activeBooking['total_amount']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- RECENT BOOKINGS TABLE -->
    <div class="user-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-gold small font-cinzel">TRIP HISTORY</span>
                <h4 class="font-cinzel mb-0">My Recent Bookings</h4>
            </div>
            <a href="user_bookings.php" class="view-all">View All <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="table-responsive">
            <table class="recent-table w-100">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Dates</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($myBookings)): ?>
                        <?php foreach (array_slice($myBookings, 0, 5) as $bkg): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($bkg['booking_code']) ?></strong></td>
                                <td><?= htmlspecialchars($bkg['vehicle_name']) ?></td>
                                <td><?= date('d M Y', strtotime($bkg['pickup_date'])) ?> – <?= date('d M Y', strtotime($bkg['return_date'])) ?></td>
                                <td><strong class="text-gold">₹<?= number_format($bkg['total_amount']) ?></strong></td>
                                <td>
                                    <span class="booking-status <?= strtolower($bkg['booking_status']) ?>">
                                        <?= htmlspecialchars($bkg['booking_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-outline-light btn-sm" onclick="viewBooking(<?= $bkg['id'] ?>)">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">You have not placed any reservations yet. <a href="user_fleet.php" class="text-gold">Browse Fleet</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- VIEW BOOKING RECEIPT MODAL -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="viewBookingTitle">Reservation Summary</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewBookingBody">
        <!-- Dynamically loaded -->
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
