<?php
$pageTitle = 'Booking Management';
$currentPage = 'bookings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$bookings = [];
$customers = [];
$vehicles = [];
$bStats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'completed' => 0];

if ($db) {
    try {
        $bRes = $db->query("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN booking_status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
                   SUM(CASE WHEN booking_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM bookings
        ")->fetch();
        if ($bRes) $bStats = $bRes;

        $bookings = $db->query("
            SELECT b.*, 
                   c.full_name as customer_name, c.email as customer_email, c.phone as customer_phone,
                   CONCAT(v.brand, ' ', v.model) as vehicle_name, v.registration_number as vehicle_reg, v.image as vehicle_image
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            JOIN vehicles v ON b.vehicle_id = v.id
            ORDER BY b.id DESC
        ")->fetchAll();

        $customers = $db->query("SELECT id, full_name, email, phone FROM customers WHERE status = 'Active' ORDER BY full_name ASC")->fetchAll();
        $vehicles = $db->query("SELECT id, brand, model, price_per_day, registration_number, status FROM vehicles ORDER BY brand ASC")->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content bookings-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="bookings-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">RESERVATIONS & RENTALS</p>
            <h1>Booking Operations</h1>
            <p class="page-description text-muted">Manage car reservations, customer trips, and active rentals.</p>
        </div>

        <button class="gold-btn" onclick="openNewBookingModal()">
            <i class="bi bi-plus-lg me-1"></i> New Booking
        </button>
    </div>

    <!-- BOOKING STATS -->
    <section class="booking-stats mb-4">
        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
                <span>Total Reservations</span>
                <strong><?= intval($bStats['total']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <span>Confirmed</span>
                <strong><?= intval($bStats['confirmed']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-clock"></i></div>
            <div>
                <span>Pending Approval</span>
                <strong><?= intval($bStats['pending']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-check2-all"></i></div>
            <div>
                <span>Completed</span>
                <strong><?= intval($bStats['completed']) ?></strong>
            </div>
        </div>
    </section>

    <!-- TOOLBAR -->
    <section class="booking-toolbar mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="booking-search flex-grow-1">
            <i class="bi bi-search"></i>
            <input type="text" id="bookingSearch" placeholder="Search booking code, customer or vehicle..." onkeyup="filterBookingTable()">
        </div>

        <select class="booking-filter" id="bookingStatusFilter" onchange="filterBookingTable()">
            <option value="all">All Booking Statuses</option>
            <option value="Confirmed">Confirmed</option>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <select class="booking-filter" id="paymentStatusFilter" onchange="filterBookingTable()">
            <option value="all">All Payment Statuses</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Partial">Partial</option>
            <option value="Refunded">Refunded</option>
        </select>
    </section>

    <!-- BOOKINGS TABLE -->
    <section class="dashboard-panel p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="recent-table w-100" id="bookingsTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Rental Period</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                        <th>Booking Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $bkg): ?>
                            <tr data-booking-id="<?= $bkg['id'] ?>"
                                data-search="<?= htmlspecialchars(strtolower($bkg['booking_code'] . ' ' . $bkg['customer_name'] . ' ' . $bkg['vehicle_name'])) ?>"
                                data-status="<?= htmlspecialchars($bkg['booking_status']) ?>"
                                data-payment="<?= htmlspecialchars($bkg['payment_status']) ?>">
                                
                                <td><strong>#<?= htmlspecialchars($bkg['booking_code']) ?></strong></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($bkg['customer_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($bkg['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($bkg['vehicle_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($bkg['vehicle_reg']) ?></div>
                                </td>
                                <td>
                                    <div><?= date('d M Y', strtotime($bkg['pickup_date'])) ?></div>
                                    <div class="text-muted small">to <?= date('d M Y', strtotime($bkg['return_date'])) ?> (<?= $bkg['rental_days'] ?>d)</div>
                                </td>
                                <td><strong>₹<?= number_format($bkg['total_amount']) ?></strong></td>
                                <td>
                                    <span class="badge bg-outline-gold text-uppercase small px-2 py-1 border border-secondary">
                                        <?= htmlspecialchars($bkg['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="booking-status <?= strtolower($bkg['booking_status']) ?>">
                                        <?= htmlspecialchars($bkg['booking_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-light btn-sm" onclick="viewBooking(<?= $bkg['id'] ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editBooking(<?= $bkg['id'] ?>)" title="Edit Booking">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($bkg['booking_status'] !== 'Cancelled'): ?>
                                            <button class="btn btn-outline-danger btn-sm" onclick="cancelBooking(<?= $bkg['id'] ?>, '#<?= htmlspecialchars($bkg['booking_code']) ?>')" title="Cancel Booking">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No bookings found in repository.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<!-- ================= CREATE / EDIT BOOKING MODAL ================= -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="bookingModalTitle">Create New Booking</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="bookingForm" onsubmit="handleSaveBooking(event)">
        <div class="modal-body p-4">
            <input type="hidden" id="bId" name="id" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Select Customer *</label>
                    <select class="form-select rydex-input" id="bCustomerId" name="customer_id" required>
                        <option value="">-- Select Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?> (<?= htmlspecialchars($c['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Select Vehicle *</label>
                    <select class="form-select rydex-input" id="bVehicleId" name="vehicle_id" onchange="calculateBookingTotal()" required>
                        <option value="">-- Select Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" data-price="<?= $v['price_per_day'] ?>">
                                <?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?> (₹<?= number_format($v['price_per_day']) ?>/day)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Pick-up Date *</label>
                    <input type="date" class="form-control rydex-input" id="bPickupDate" name="pickup_date" value="<?= date('Y-m-d') ?>" onchange="calculateBookingTotal()" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Return Date *</label>
                    <input type="date" class="form-control rydex-input" id="bReturnDate" name="return_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" onchange="calculateBookingTotal()" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small">Payment Method</label>
                    <select class="form-select rydex-input" id="bPaymentMethod" name="payment_method">
                        <option value="Credit Card">Credit Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small">Payment Status</label>
                    <select class="form-select rydex-input" id="bPaymentStatus" name="payment_status">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Refunded">Refunded</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small">Booking Status</label>
                    <select class="form-select rydex-input" id="bBookingStatus" name="booking_status">
                        <option value="Confirmed">Confirmed</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Price Summary Box -->
                <div class="col-md-12">
                    <div class="p-3 bg-dark border border-secondary rounded d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Calculated Rental Duration</div>
                            <strong id="bDaysSummary" class="text-white">3 Days</strong>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Estimated Total Price</div>
                            <strong id="bTotalSummary" class="text-gold fs-4">₹0</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label text-muted small">Special Rental Notes</label>
                    <textarea class="form-control rydex-input" id="bNotes" name="notes" rows="2" placeholder="Airport pick-up requests, insurance options, driver requirements..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="gold-btn">Confirm Booking</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= VIEW DYNAMIC BOOKING MODAL (BUG #32 FIXED) ================= -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="viewBookingTitle">Reservation Summary</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewBookingBody">
        <!-- Loaded dynamically for EXACT booking row clicked -->
      </div>
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
