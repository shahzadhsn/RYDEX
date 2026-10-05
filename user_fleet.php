<?php
$pageTitle = 'Explore Fleet';
$currentPage = 'fleet';
require_once __DIR__ . '/includes/user_header.php';
require_once __DIR__ . '/includes/user_navbar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$vehicles = [];

if ($db) {
    try {
        $vehicles = $db->query("SELECT * FROM vehicles WHERE status != 'Unavailable' ORDER BY id ASC")->fetchAll();
    } catch (Exception $e) {}
}
?>

<div class="container py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <span class="text-gold small font-cinzel">EXQUISITE COLLECTION</span>
            <h1 class="font-cinzel text-white mb-1">Flagship Luxury Fleet</h1>
            <p class="text-muted small mb-0">Select your preferred supercar or executive luxury sedan for an unprecedented drive.</p>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="user-card p-3 mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="flex-grow-1 position-relative">
            <input type="text" id="userFleetSearch" class="form-control rydex-input ps-5" placeholder="Search brand, model, or specs..." onkeyup="filterUserFleet()">
            <i class="bi bi-search position-absolute text-gold" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
        </div>

        <select class="form-select rydex-input w-auto" id="userCategoryFilter" onchange="filterUserFleet()">
            <option value="all">All Categories</option>
            <option value="Sports">Sports</option>
            <option value="Grand Tourer">Grand Tourer</option>
            <option value="Supercar">Supercar</option>
            <option value="Luxury">Luxury</option>
            <option value="SUV">SUV</option>
        </select>
    </div>

    <!-- FLEET GRID -->
    <div class="row g-4" id="userVehicleGrid">
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $car): ?>
                <div class="col-lg-4 col-md-6 user-vehicle-item"
                     data-brand="<?= htmlspecialchars($car['brand']) ?>"
                     data-category="<?= htmlspecialchars($car['category']) ?>"
                     data-search="<?= htmlspecialchars(strtolower($car['brand'] . ' ' . $car['model'] . ' ' . $car['category'])) ?>">
                    
                    <div class="user-card h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative p-4 text-center bg-dark" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                            <span class="vehicle-status <?= strtolower($car['status']) ?>-status position-absolute top-0 end-0 m-3 px-3 py-1">
                                <?= htmlspecialchars($car['status']) ?>
                            </span>
                            <img src="<?= htmlspecialchars($car['image']) ?>" class="img-fluid" style="max-height: 150px; object-fit: contain;" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                        </div>

                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <span class="vehicle-category mb-1"><?= htmlspecialchars(strtoupper($car['category'])) ?></span>
                            <h4 class="font-cinzel text-white mb-2"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                            <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($car['description'] ?? 'High performance vehicle.') ?></p>

                            <div class="d-flex justify-content-between align-items-center text-muted small mb-3 border-top border-secondary pt-3">
                                <span><i class="bi bi-gear-wide-connected me-1 text-gold"></i><?= htmlspecialchars($car['transmission']) ?></span>
                                <span><i class="bi bi-fuel-pump me-1 text-gold"></i><?= htmlspecialchars($car['fuel_type']) ?></span>
                                <span><i class="bi bi-people me-1 text-gold"></i><?= htmlspecialchars($car['seats']) ?> Seats</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-gold fs-4">₹<?= number_format($car['price_per_day']) ?></strong>
                                    <span class="text-muted small">/ day</span>
                                </div>

                                <?php if ($car['status'] === 'Available'): ?>
                                    <button class="gold-btn btn-sm" onclick="openUserBookModal(<?= $car['id'] ?>, '<?= htmlspecialchars(addslashes($car['brand'] . ' ' . $car['model'])) ?>', <?= $car['price_per_day'] ?>)">
                                        Book Now <i class="bi bi-arrow-right"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        Currently Rented
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <p>No vehicles found.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- CUSTOMER BOOKING MODAL -->
<div class="modal fade" id="userBookModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="userBookModalTitle">Reserve Vehicle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="userBookForm" onsubmit="handleUserBookSubmit(event)">
        <div class="modal-body p-4">
            <input type="hidden" id="uVid" name="vehicle_id" value="0">
            <input type="hidden" id="uDailyPrice" value="0">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Pick-up Date *</label>
                    <input type="date" class="form-control rydex-input" id="uPickupDate" name="pickup_date" value="<?= date('Y-m-d') ?>" onchange="calcUserTotal()" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Return Date *</label>
                    <input type="date" class="form-control rydex-input" id="uReturnDate" name="return_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" onchange="calcUserTotal()" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Preferred Payment Method</label>
                    <select class="form-select rydex-input" id="uPaymentMethod" name="payment_method">
                        <option value="Credit Card">Credit Card</option>
                        <option value="UPI">UPI Payment</option>
                        <option value="Bank Transfer">Bank Wire Transfer</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Pick-up Location</label>
                    <input type="text" class="form-control rydex-input" value="RYDEX Luxury Hub (Bandra, Mumbai)" readonly>
                </div>

                <!-- Price Calculation Box -->
                <div class="col-12 mt-3">
                    <div class="p-3 bg-dark border border-secondary rounded d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Estimated Duration</div>
                            <strong id="uDaysSummary" class="text-white">3 Days</strong>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Total Rental Amount</div>
                            <strong id="uTotalSummary" class="text-gold fs-4">₹0</strong>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small">Special Requests / Notes</label>
                    <textarea class="form-control rydex-input" id="uNotes" name="notes" rows="2" placeholder="Chauffeur options, airport delivery, special instructions..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="gold-btn">Confirm Reservation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterUserFleet() {
    const search = document.getElementById('userFleetSearch').value.toLowerCase().trim();
    const cat = document.getElementById('userCategoryFilter').value;
    const items = document.querySelectorAll('.user-vehicle-item');

    items.forEach(item => {
        const itemSearch = item.getAttribute('data-search') || '';
        const itemCat = item.getAttribute('data-category') || '';
        const sMatch = !search || itemSearch.includes(search);
        const cMatch = cat === 'all' || itemCat === cat;
        item.style.display = (sMatch && cMatch) ? '' : 'none';
    });
}

function openUserBookModal(vid, name, price) {
    <?php if (!isUserLoggedIn()): ?>
        window.location.href = 'user_login.php';
        return;
    <?php endif; ?>

    document.getElementById('uVid').value = vid;
    document.getElementById('uDailyPrice').value = price;
    document.getElementById('userBookModalTitle').textContent = `Reserve ${name}`;
    calcUserTotal();

    const modal = new bootstrap.Modal(document.getElementById('userBookModal'));
    modal.show();
}

function calcUserTotal() {
    const pricePerDay = parseFloat(document.getElementById('uDailyPrice').value || 0);
    const pickup = new Date(document.getElementById('uPickupDate').value);
    const returnD = new Date(document.getElementById('uReturnDate').value);

    const daysSummary = document.getElementById('uDaysSummary');
    const totalSummary = document.getElementById('uTotalSummary');

    if (isNaN(pickup.getTime()) || isNaN(returnD.getTime()) || returnD <= pickup) {
        daysSummary.textContent = 'Invalid Dates';
        totalSummary.textContent = '₹0';
        return;
    }

    const diffDays = Math.ceil(Math.abs(returnD - pickup) / (1000 * 60 * 60 * 24));
    daysSummary.textContent = `${diffDays} Day${diffDays > 1 ? 's' : ''}`;
    const total = diffDays * pricePerDay;
    totalSummary.textContent = `₹${total.toLocaleString('en-IN')}`;
}

async function handleUserBookSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    const formData = {
        vehicle_id: document.getElementById('uVid').value,
        pickup_date: document.getElementById('uPickupDate').value,
        return_date: document.getElementById('uReturnDate').value,
        payment_method: document.getElementById('uPaymentMethod').value,
        notes: document.getElementById('uNotes').value
    };

    try {
        const res = await fetch('api/user_bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const data = await res.json();

        if (data.success) {
            showRydexToast(data.message, 'success');
            const modalEl = document.getElementById('userBookModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => window.location.href = 'user_bookings.php', 1200);
        } else {
            showRydexToast(data.message || 'Error creating reservation.', 'error');
            submitBtn.disabled = false;
        }
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
        submitBtn.disabled = false;
    }
}
</script>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
