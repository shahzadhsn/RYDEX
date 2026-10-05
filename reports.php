<?php
$pageTitle = 'Analytics & Reports';
$currentPage = 'reports';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

$summary = ['total_bookings' => 0, 'total_revenue' => 0, 'completed_count' => 0, 'cancelled_count' => 0];
$topVehicles = [];
$monthlyRevenue = [];
$statusDist = [];

if ($db) {
    try {
        $summary = $db->query("
            SELECT COUNT(*) as total_bookings,
                   COALESCE(SUM(total_amount), 0) as total_revenue,
                   SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                   SUM(CASE WHEN booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_count
            FROM bookings
        ")->fetch();

        $topVehicles = $db->query("
            SELECT CONCAT(v.brand, ' ', v.model) as vehicle_name, v.category, v.image,
                   COUNT(b.id) as total_rentals,
                   COALESCE(SUM(b.total_amount), 0) as total_revenue
            FROM vehicles v
            JOIN bookings b ON v.id = b.vehicle_id
            WHERE b.booking_status != 'Cancelled'
            GROUP BY v.id
            ORDER BY total_revenue DESC
            LIMIT 5
        ")->fetchAll();

        $monthlyRevenue = $db->query("
            SELECT DATE_FORMAT(pickup_date, '%b %Y') as month_name, 
                   COALESCE(SUM(total_amount), 0) as monthly_total,
                   COUNT(id) as booking_count
            FROM bookings
            WHERE booking_status != 'Cancelled'
            GROUP BY YEAR(pickup_date), MONTH(pickup_date)
            ORDER BY pickup_date ASC
        ")->fetchAll();

        $statusDist = $db->query("
            SELECT booking_status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM bookings
            GROUP BY booking_status
        ")->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content reports-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="reports-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">BUSINESS INTELLIGENCE</p>
            <h1>Analytics & Reports</h1>
            <p class="page-description text-muted">Comprehensive revenue analysis, fleet utilization, and performance reports.</p>
        </div>

        <a href="api/reports.php?action=export_csv" class="gold-btn text-decoration-none">
            <i class="bi bi-download me-1"></i> Export CSV Report
        </a>
    </div>

    <!-- DATE RANGE FILTER TOOLBAR -->
    <section class="booking-toolbar mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-gold"></i>
            <span class="text-muted small">DATE FILTER:</span>
        </div>

        <select class="booking-filter" id="reportDateRange" onchange="loadReportData(this.value)">
            <option value="this_month">This Month</option>
            <option value="today">Today</option>
            <option value="this_week">This Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_3_months">Last 3 Months</option>
            <option value="this_year">This Year</option>
            <option value="all_time">All Time</option>
        </select>
    </section>

    <!-- SUMMARY STAT CARDS -->
    <section class="booking-stats mb-4">
        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <span>Total Generated Revenue</span>
                <strong id="repTotalRevenue">₹<?= number_format($summary['total_revenue']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-journal-check"></i></div>
            <div>
                <span>Total Bookings Logged</span>
                <strong id="repTotalBookings"><?= intval($summary['total_bookings']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-trophy"></i></div>
            <div>
                <span>Completed Trips</span>
                <strong id="repCompleted"><?= intval($summary['completed_count']) ?></strong>
            </div>
        </div>
    </section>

    <!-- CHARTS & TABLES GRID -->
    <div class="row g-4">
        <!-- Revenue Breakdown Table -->
        <div class="col-lg-7">
            <div class="dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">REVENUE TRENDS</span>
                        <h3>Monthly Earnings Summary</h3>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="recent-table w-100">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Total Trips</th>
                                <th>Revenue Collected</th>
                            </tr>
                        </thead>
                        <tbody id="repMonthlyBody">
                            <?php if (!empty($monthlyRevenue)): ?>
                                <?php foreach ($monthlyRevenue as $m): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($m['month_name']) ?></strong></td>
                                        <td><?= intval($m['booking_count']) ?> rentals</td>
                                        <td><strong class="text-gold">₹<?= number_format($m['monthly_total']) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">No monthly data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-lg-5">
            <div class="dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">BOOKING RATIOS</span>
                        <h3>Status Distribution</h3>
                    </div>
                </div>

                <div class="availability-list mt-3" id="repStatusList">
                    <?php foreach ($statusDist as $sd): ?>
                        <div class="availability-item py-3">
                            <span>
                                <i class="status-dot <?= strtolower($sd['booking_status']) ?>"></i>
                                <?= htmlspecialchars($sd['booking_status']) ?>
                            </span>
                            <div>
                                <strong><?= intval($sd['count']) ?> bookings</strong>
                                <div class="text-muted small text-end">₹<?= number_format($sd['total']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Most Rented Luxury Cars -->
        <div class="col-12">
            <div class="dashboard-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-label">HIGH PERFORMERS</span>
                        <h3>Most Rented Luxury Vehicles</h3>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="recent-table w-100">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Vehicle</th>
                                <th>Category</th>
                                <th>Total Rentals</th>
                                <th>Total Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody id="repTopVehiclesBody">
                            <?php if (!empty($topVehicles)): $rank = 1; ?>
                                <?php foreach ($topVehicles as $tv): ?>
                                    <tr>
                                        <td><span class="badge bg-gold text-dark font-cinzel">#<?= $rank++ ?></span></td>
                                        <td><strong><?= htmlspecialchars($tv['vehicle_name']) ?></strong></td>
                                        <td><span class="vehicle-category"><?= htmlspecialchars(strtoupper($tv['category'])) ?></span></td>
                                        <td><?= intval($tv['total_rentals']) ?> times</td>
                                        <td><strong class="text-gold">₹<?= number_format($tv['total_revenue']) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No vehicle performance data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
