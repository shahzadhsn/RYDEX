<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

$vStats = ['total' => 10, 'available' => 6, 'rented' => 3, 'maintenance' => 1];
$bStats = ['total' => 15, 'pending' => 3, 'confirmed' => 5, 'completed' => 6, 'cancelled' => 1];
$totalCustomers = 10;
$totalRevenue = 575500;
$recentBookings = [];

if ($db) {
    try {
        $vRes = $db->query("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
                   SUM(CASE WHEN status = 'Rented' THEN 1 ELSE 0 END) as rented,
                   SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
            FROM vehicles
        ")->fetch();
        if ($vRes) $vStats = $vRes;

        $bRes = $db->query("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN booking_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN booking_status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
                   SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM bookings
        ")->fetch();
        if ($bRes) $bStats = $bRes;

        $totalCustomers = $db->query("SELECT COUNT(*) FROM customers WHERE status = 'Active'")->fetchColumn();
        $totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE booking_status != 'Cancelled' AND payment_status = 'Paid'")->fetchColumn();

        $recentBookings = $db->query("
            SELECT b.id, b.booking_code, c.full_name as customer_name, CONCAT(v.brand, ' ', v.model) as vehicle_name,
                   DATE_FORMAT(b.pickup_date, '%d %b') as pdate, DATE_FORMAT(b.return_date, '%d %b') as rdate,
                   b.total_amount, b.booking_status, b.payment_status
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            JOIN vehicles v ON b.vehicle_id = v.id
            ORDER BY b.id DESC
            LIMIT 5
        ")->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- ================= STAT CARDS ================= -->
    <section class="dashboard-stats">

        <!-- Total Vehicles -->
        <div class="dashboard-stat-card">
            <div class="stat-card-top">
                <div class="stat-icon"><i class="bi bi-car-front"></i></div>
                <span class="stat-growth positive">+12.5%</span>
            </div>
            <p>Total Vehicles</p>
            <h2><?= intval($vStats['total']) ?></h2>
            <span class="stat-description"><?= intval($vStats['available']) ?> Available for Rent</span>
        </div>

        <!-- Total Bookings -->
        <div class="dashboard-stat-card">
            <div class="stat-card-top">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <span class="stat-growth positive">+8.2%</span>
            </div>
            <p>Total Bookings</p>
            <h2><?= intval($bStats['total']) ?></h2>
            <span class="stat-description"><?= intval($bStats['confirmed']) ?> Active Confirmed</span>
        </div>

        <!-- Total Revenue -->
        <div class="dashboard-stat-card">
            <div class="stat-card-top">
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                <span class="stat-growth positive">+15.7%</span>
            </div>
            <p>Total Revenue</p>
            <h2>₹<?= number_format($totalRevenue) ?></h2>
            <span class="stat-description">Paid Rentals Ledger</span>
        </div>

        <!-- Customers -->
        <div class="dashboard-stat-card">
            <div class="stat-card-top">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <span class="stat-growth positive">+10.4%</span>
            </div>
            <p>Active Customers</p>
            <h2><?= intval($totalCustomers) ?></h2>
            <span class="stat-description">Registered Luxury Clients</span>
        </div>

    </section>


    <!-- ================= CHART + AVAILABILITY ================= -->
    <section class="dashboard-main-grid">

        <!-- REVENUE CHART -->
        <div class="dashboard-panel revenue-panel">
            <div class="panel-header">
                <div>
                    <span class="panel-label">OPERATIONAL PERFORMANCE</span>
                    <h3>Revenue Overview</h3>
                </div>
                <select class="chart-select">
                    <option>Last 30 Days</option>
                    <option>Last 3 Months</option>
                    <option>This Year</option>
                </select>
            </div>

            <div class="chart-area">
                <div class="chart-y-axis">
                    <span>₹150K</span>
                    <span>₹100K</span>
                    <span>₹50K</span>
                    <span>₹25K</span>
                    <span>₹0</span>
                </div>

                <div class="chart">
                    <div class="chart-grid-lines">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>

                    <svg viewBox="0 0 700 250" preserveAspectRatio="none" class="revenue-chart">
                        <defs>
                            <linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#c8a45d" stop-opacity=".35" />
                                <stop offset="100%" stop-color="#c8a45d" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="M0,205 C45,190 65,180 105,185 C145,190 165,150 210,158 C255,166 270,125 315,135 C360,145 385,95 425,110 C465,125 490,80 530,92 C570,104 600,60 640,72 C665,78 685,45 700,52 L700,250 L0,250 Z" fill="url(#chartGradient)" />
                        <path d="M0,205 C45,190 65,180 105,185 C145,190 165,150 210,158 C255,166 270,125 315,135 C360,145 385,95 425,110 C465,125 490,80 530,92 C570,104 600,60 640,72 C665,78 685,45 700,52" fill="none" stroke="#c8a45d" stroke-width="3" vector-effect="non-scaling-stroke" />
                    </svg>

                    <div class="chart-x-axis">
                        <span>Jun</span>
                        <span>Jul</span>
                        <span>Aug</span>
                        <span>Sep</span>
                        <span>Oct</span>
                        <span>Nov</span>
                        <span>Dec</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- VEHICLE AVAILABILITY -->
        <div class="dashboard-panel availability-panel">
            <div class="panel-header">
                <div>
                    <span class="panel-label">FLEET UTILIZATION</span>
                    <h3>Vehicle Status</h3>
                </div>
                <a href="fleet.php" class="panel-link">View Fleet</a>
            </div>

            <div class="availability-circle">
                <div class="circle-inner">
                    <strong><?= intval($vStats['total']) ?></strong>
                    <span>Total Cars</span>
                </div>
            </div>

            <div class="availability-list">
                <div class="availability-item">
                    <span><i class="status-dot available"></i> Available</span>
                    <strong><?= intval($vStats['available']) ?></strong>
                </div>
                <div class="availability-item">
                    <span><i class="status-dot rented"></i> Rented</span>
                    <strong><?= intval($vStats['rented']) ?></strong>
                </div>
                <div class="availability-item">
                    <span><i class="status-dot service"></i> Maintenance</span>
                    <strong><?= intval($vStats['maintenance']) ?></strong>
                </div>
            </div>
        </div>

    </section>


    <!-- ================= RECENT BOOKINGS ================= -->
    <section class="dashboard-panel recent-bookings">
        <div class="panel-header">
            <div>
                <span class="panel-label">LIVE OPERATIONS</span>
                <h3>Recent Rental Bookings</h3>
            </div>
            <a href="bookings.php" class="panel-link">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="recent-table-wrapper">
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Booking Code</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Rental Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $bkg): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($bkg['booking_code']) ?></strong></td>
                                <td><?= htmlspecialchars($bkg['customer_name']) ?></td>
                                <td><?= htmlspecialchars($bkg['vehicle_name']) ?></td>
                                <td><?= htmlspecialchars($bkg['pdate']) ?> – <?= htmlspecialchars($bkg['rdate']) ?></td>
                                <td>₹<?= number_format($bkg['total_amount']) ?></td>
                                <td>
                                    <span class="booking-status <?= strtolower($bkg['booking_status']) ?>">
                                        <?= htmlspecialchars($bkg['booking_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No recent bookings recorded.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
