<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
$db = getDB();

$featuredVehicles = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM vehicles WHERE status = 'Available' ORDER BY id ASC LIMIT 3");
        $featuredVehicles = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($featuredVehicles)) {
    $featuredVehicles = [
        [
            'id' => 1, 'category' => 'SPORTS', 'brand' => 'BMW', 'model' => 'M4 Competition',
            'description' => 'Experience exceptional performance with unmistakable German engineering.',
            'price_per_day' => 8500, 'image' => 'images/bmw-m4.png'
        ],
        [
            'id' => 2, 'category' => 'GRAND TOURER', 'brand' => 'Mercedes', 'model' => 'AMG GT',
            'description' => 'Refined luxury combined with breathtaking performance.',
            'price_per_day' => 12000, 'image' => 'images/mercedes-amg.png'
        ],
        [
            'id' => 3, 'category' => 'SUPERCAR', 'brand' => 'Porsche', 'model' => '911 Turbo S',
            'description' => 'Iconic design and exhilarating performance in perfect harmony.',
            'price_per_day' => 15000, 'image' => 'images/porsche-911.png'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RYDEX | Experience Luxury Without Limits</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar rydex-navbar">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand rydex-logo">RYDEX</a>

            <div class="nav-right">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="user_fleet.php" class="nav-link">Fleet</a>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <a href="user_dashboard.php" class="nav-link">My Dashboard</a>
                    <a href="user_bookings.php" class="nav-link">My Bookings</a>
                <?php else: ?>
                    <a href="user_login.php" class="nav-link">Customer Login</a>
                    <a href="user_register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <a href="user_dashboard.php" class="nav-login gold-btn py-2 px-3 text-dark text-decoration-none fw-semibold">
                        <i class="bi bi-person-circle me-1"></i> Account
                    </a>
                <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="dashboard.php" class="nav-login gold-btn py-2 px-3 text-dark text-decoration-none fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Portal
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-login text-muted text-decoration-none small">
                        <i class="bi bi-shield-lock me-1"></i> Admin Portal
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <!-- ================= HERO SECTION ================= -->
    <section class="hero-section">
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <p class="hero-small-text">PREMIUM CAR RENTAL</p>

            <h1>Experience Luxury <br>Without Limits.</h1>

            <p class="hero-description">
                Discover an exclusive collection of high-performance luxury vehicles tailored for exceptional drives.
            </p>

            <div class="hero-buttons">
                <a href="fleet.php" class="gold-btn">
                    Explore Fleet <i class="bi bi-arrow-right"></i>
                </a>
                <a href="bookings.php" class="outline-btn">Book a Car</a>
            </div>
        </div>

        <!-- ================= SEARCH BOX ================= -->
        <div class="hero-search">
            <div class="search-item">
                <i class="bi bi-geo-alt"></i>
                <div>
                    <span>LOCATION</span>
                    <strong>Mumbai, MH</strong>
                </div>
            </div>

            <div class="search-divider"></div>

            <div class="search-item">
                <i class="bi bi-calendar3"></i>
                <div>
                    <span>PICK-UP DATE</span>
                    <input type="date" id="heroPickup" class="bg-transparent border-0 text-white p-0" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="search-divider"></div>

            <div class="search-item">
                <i class="bi bi-calendar-check"></i>
                <div>
                    <span>RETURN DATE</span>
                    <input type="date" id="heroReturn" class="bg-transparent border-0 text-white p-0" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>
            </div>

            <a href="bookings.php" class="search-button text-decoration-none d-flex align-items-center justify-content-center">
                <i class="bi bi-search me-1"></i> Search
            </a>
        </div>
    </section>


    <!-- ================= FEATURED SECTION ================= -->
    <section class="featured-section" id="collection">
        <div class="section-heading">
            <div>
                <span class="section-label">OUR COLLECTION</span>
                <h2>Featured Vehicles</h2>
            </div>
            <a href="fleet.php" class="view-all">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="featured-grid">
            <?php foreach ($featuredVehicles as $car): ?>
                <div class="featured-card">
                    <div class="featured-info">
                        <span class="car-category"><?= htmlspecialchars(strtoupper($car['category'])) ?></span>
                        <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                        <p><?= htmlspecialchars($car['description'] ?? 'High performance luxury vehicle.') ?></p>
                        <div class="car-price">
                            <strong>₹<?= number_format($car['price_per_day']) ?></strong>
                            <span>/ day</span>
                        </div>
                        <a href="bookings.php?vehicle_id=<?= $car['id'] ?>" class="card-btn">
                            Book Now <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="featured-image">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- ================= STATS ================= -->
    <section class="stats-section">
        <div class="stat-box">
            <i class="bi bi-car-front"></i>
            <div>
                <strong>10+</strong>
                <span>Supercars & Luxury Sedans</span>
            </div>
        </div>

        <div class="stat-box">
            <i class="bi bi-people"></i>
            <div>
                <strong>2,500+</strong>
                <span>Verified Clients</span>
            </div>
        </div>

        <div class="stat-box">
            <i class="bi bi-geo-alt"></i>
            <div>
                <strong>15+</strong>
                <span>Pick-up Hubs</span>
            </div>
        </div>

        <div class="stat-box">
            <i class="bi bi-star-fill"></i>
            <div>
                <strong>4.9/5</strong>
                <span>Customer Satisfaction</span>
            </div>
        </div>
    </section>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
