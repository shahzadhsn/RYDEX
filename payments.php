<?php
$pageTitle = 'Payments Ledger';
$currentPage = 'payments';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$payments = [];
$pStats = ['total_received' => 0, 'pending_amount' => 0, 'paid_count' => 0];

if ($db) {
    try {
        $payments = $db->query("
            SELECT p.*, b.booking_code, c.full_name as customer_name, c.email as customer_email,
                   CONCAT(v.brand, ' ', v.model) as vehicle_name
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN customers c ON p.customer_id = c.id
            JOIN vehicles v ON b.vehicle_id = v.id
            ORDER BY p.id DESC
        ")->fetchAll();

        foreach ($payments as $pay) {
            if ($pay['payment_status'] === 'Paid') {
                $pStats['total_received'] += floatval($pay['amount']);
                $pStats['paid_count']++;
            } else if ($pay['payment_status'] === 'Pending') {
                $pStats['pending_amount'] += floatval($pay['amount']);
            }
        }
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content payments-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="payments-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">FINANCIAL TRANSACTIONS</p>
            <h1>Payments Ledger</h1>
            <p class="page-description text-muted">Monitor client transactions, gateway receipts, and refund requests.</p>
        </div>
    </div>

    <!-- PAYMENT STATS -->
    <section class="booking-stats mb-4">
        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-wallet2"></i></div>
            <div>
                <span>Total Collected</span>
                <strong>₹<?= number_format($pStats['total_received']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <span>Pending Receivables</span>
                <strong>₹<?= number_format($pStats['pending_amount']) ?></strong>
            </div>
        </div>

        <div class="booking-stat-card">
            <div class="booking-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <span>Settled Transactions</span>
                <strong><?= intval($pStats['paid_count']) ?></strong>
            </div>
        </div>
    </section>

    <!-- SEARCH & FILTER TOOLBAR -->
    <section class="booking-toolbar mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="booking-search flex-grow-1">
            <i class="bi bi-search"></i>
            <input type="text" id="paymentSearch" placeholder="Search payment code, transaction ID, booking or customer..." onkeyup="filterPaymentTable()">
        </div>

        <select class="booking-filter" id="payMethodFilter" onchange="filterPaymentTable()">
            <option value="all">All Methods</option>
            <option value="Credit Card">Credit Card</option>
            <option value="UPI">UPI</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Debit Card">Debit Card</option>
            <option value="Cash">Cash</option>
        </select>

        <select class="booking-filter" id="payStatusFilter" onchange="filterPaymentTable()">
            <option value="all">All Statuses</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Partial">Partial</option>
            <option value="Refunded">Refunded</option>
            <option value="Failed">Failed</option>
        </select>
    </section>

    <!-- PAYMENTS TABLE -->
    <section class="dashboard-panel p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="recent-table w-100" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Booking Code</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction Ref</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th class="text-end">Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $p): ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($p['payment_code'] . ' ' . $p['transaction_id'] . ' ' . $p['booking_code'] . ' ' . $p['customer_name'])) ?>"
                                data-method="<?= htmlspecialchars($p['payment_method']) ?>"
                                data-status="<?= htmlspecialchars($p['payment_status']) ?>">
                                <td><strong>#<?= htmlspecialchars($p['payment_code']) ?></strong></td>
                                <td><a href="bookings.php" class="text-gold">#<?= htmlspecialchars($p['booking_code']) ?></a></td>
                                <td><?= htmlspecialchars($p['customer_name']) ?></td>
                                <td><?= htmlspecialchars($p['vehicle_name']) ?></td>
                                <td><strong class="text-gold">₹<?= number_format($p['amount']) ?></strong></td>
                                <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                                <td><code class="text-muted small"><?= htmlspecialchars($p['transaction_id']) ?></code></td>
                                <td><?= date('d M Y H:i', strtotime($p['payment_date'])) ?></td>
                                <td>
                                    <span class="badge <?= $p['payment_status'] === 'Paid' ? 'bg-success' : ($p['payment_status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') ?> px-2 py-1">
                                        <?= htmlspecialchars($p['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <select class="form-select form-select-sm rydex-input py-1 px-2 d-inline-block w-auto" onchange="updatePaymentStatus(<?= $p['id'] ?>, this.value)">
                                        <option value="Paid" <?= $p['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="Pending" <?= $p['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Refunded" <?= $p['payment_status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                                        <option value="Failed" <?= $p['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No payment transactions recorded.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
