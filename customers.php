<?php
$pageTitle = 'Customer Directory';
$currentPage = 'customers';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$customers = [];
$cStats = ['total' => 0, 'active' => 0, 'total_spent' => 0];

if ($db) {
    try {
        $customers = $db->query("
            SELECT c.*,
                   COUNT(b.id) as total_bookings,
                   COALESCE(SUM(CASE WHEN b.booking_status != 'Cancelled' THEN b.total_amount ELSE 0 END), 0) as total_spent
            FROM customers c
            LEFT JOIN bookings b ON c.id = b.customer_id
            GROUP BY c.id
            ORDER BY c.id DESC
        ")->fetchAll();

        $cStats['total'] = count($customers);
        foreach ($customers as $cust) {
            if ($cust['status'] === 'Active') $cStats['active']++;
            $cStats['total_spent'] += floatval($cust['total_spent']);
        }
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content customers-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="customers-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">CLIENT MANAGEMENT</p>
            <h1>Customers Directory</h1>
            <p class="page-description text-muted">Manage registered luxury clients, driving permits, and rental history.</p>
        </div>

        <button class="gold-btn" onclick="openAddCustomerModal()">
            <i class="bi bi-person-plus me-1"></i> Add Customer
        </button>
    </div>

    <!-- CUSTOMER STATS -->
    <section class="booking-stats mb-4">
        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <span>Total Registered</span>
                <strong><?= intval($cStats['total']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <span>Active Accounts</span>
                <strong><?= intval($cStats['active']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <span>Lifetime Client Revenue</span>
                <strong>₹<?= number_format($cStats['total_spent']) ?></strong>
            </div>
        </div>
    </section>

    <!-- SEARCH TOOLBAR -->
    <section class="booking-toolbar mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="booking-search flex-grow-1">
            <i class="bi bi-search"></i>
            <input type="text" id="customerSearch" placeholder="Search customer name, email, phone or license number..." onkeyup="filterCustomerTable()">
        </div>

        <select class="booking-filter" id="customerStatusFilter" onchange="filterCustomerTable()">
            <option value="all">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </section>

    <!-- CUSTOMERS TABLE -->
    <section class="dashboard-panel p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="recent-table w-100" id="customersTable">
                <thead>
                    <tr>
                        <th>Client ID</th>
                        <th>Full Name</th>
                        <th>Contact Details</th>
                        <th>Driving License</th>
                        <th>City / State</th>
                        <th>Total Bookings</th>
                        <th>Total Spent</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $c): ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($c['full_name'] . ' ' . $c['email'] . ' ' . $c['phone'] . ' ' . $c['license_number'] . ' ' . $c['customer_code'])) ?>"
                                data-status="<?= htmlspecialchars($c['status']) ?>">
                                <td><strong>#<?= htmlspecialchars($c['customer_code']) ?></strong></td>
                                <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                                <td>
                                    <div><?= htmlspecialchars($c['email']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($c['phone']) ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($c['license_number']) ?></div>
                                    <div class="text-muted small">Exp: <?= date('d M Y', strtotime($c['license_expiry'])) ?></div>
                                </td>
                                <td><?= htmlspecialchars($c['city']) ?>, <?= htmlspecialchars($c['state']) ?></td>
                                <td><span class="badge bg-dark border border-secondary"><?= intval($c['total_bookings']) ?> rentals</span></td>
                                <td><strong class="text-gold">₹<?= number_format($c['total_spent']) ?></strong></td>
                                <td>
                                    <span class="badge <?= $c['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> px-2 py-1">
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-light btn-sm" onclick="viewCustomer(<?= $c['id'] ?>)" title="View Profile & History">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="editCustomer(<?= $c['id'] ?>)" title="Edit Customer">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deactivateCustomer(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['full_name'])) ?>')" title="Deactivate Account">
                                            <i class="bi bi-person-x"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No registered customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<!-- ================= ADD / EDIT CUSTOMER MODAL ================= -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="customerModalTitle">Register New Customer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="customerForm" onsubmit="handleSaveCustomer(event)">
        <div class="modal-body p-4">
            <input type="hidden" id="cId" name="id" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Full Name *</label>
                    <input type="text" class="form-control rydex-input" id="cName" name="full_name" placeholder="e.g. Ahmed Khan" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Email Address *</label>
                    <input type="email" class="form-control rydex-input" id="cEmail" name="email" placeholder="ahmed@example.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Phone Number *</label>
                    <input type="text" class="form-control rydex-input" id="cPhone" name="phone" placeholder="+91 98201 12345" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Driving License Number *</label>
                    <input type="text" class="form-control rydex-input" id="cLicenseNo" name="license_number" placeholder="DL-MH02-2021-9988" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">License Expiry Date *</label>
                    <input type="date" class="form-control rydex-input" id="cLicenseExp" name="license_expiry" value="2029-12-31" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Account Status</label>
                    <select class="form-select rydex-input" id="cStatus" name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">City</label>
                    <input type="text" class="form-control rydex-input" id="cCity" name="city" value="Mumbai">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">State</label>
                    <input type="text" class="form-control rydex-input" id="cState" name="state" value="Maharashtra">
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted small">Residential Address</label>
                    <textarea class="form-control rydex-input" id="cAddress" name="address" rows="2" placeholder="Street name, building, landmark..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="gold-btn">Save Customer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= VIEW CUSTOMER HISTORY MODAL ================= -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="viewCustomerTitle">Client Profile & Rental History</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewCustomerBody">
        <!-- Dynamically loaded -->
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
