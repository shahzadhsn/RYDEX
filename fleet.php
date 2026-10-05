<?php
$pageTitle = 'Fleet Management';
$currentPage = 'fleet';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$vehicles = [];
$vStats = ['total' => 0, 'available' => 0, 'rented' => 0, 'maintenance' => 0];

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

        $vehicles = $db->query("SELECT * FROM vehicles ORDER BY id DESC")->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content fleet-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="fleet-page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="dashboard-greeting">VEHICLE MANAGEMENT</p>
            <h1>Fleet Directory</h1>
            <p class="page-description text-muted">Manage, track, and optimize your luxury vehicle collection.</p>
        </div>

        <button class="gold-btn" onclick="openAddVehicleModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Vehicle
        </button>
    </div>

    <!-- FLEET STATS -->
    <section class="fleet-stats mb-4">
        <div class="fleet-stat-card">
            <div class="fleet-stat-icon"><i class="bi bi-car-front"></i></div>
            <div>
                <span>Total Fleet</span>
                <strong><?= intval($vStats['total']) ?></strong>
            </div>
        </div>

        <div class="fleet-stat-card">
            <div class="fleet-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <span>Available</span>
                <strong><?= intval($vStats['available']) ?></strong>
            </div>
        </div>

        <div class="fleet-stat-card">
            <div class="fleet-stat-icon"><i class="bi bi-key"></i></div>
            <div>
                <span>Rented Out</span>
                <strong><?= intval($vStats['rented']) ?></strong>
            </div>
        </div>

        <div class="fleet-stat-card">
            <div class="fleet-stat-icon"><i class="bi bi-tools"></i></div>
            <div>
                <span>In Service</span>
                <strong><?= intval($vStats['maintenance']) ?></strong>
            </div>
        </div>
    </section>

    <!-- SEARCH & FILTERS -->
    <section class="fleet-toolbar mb-4 d-flex gap-3 flex-wrap align-items-center">
        <div class="fleet-search flex-grow-1">
            <i class="bi bi-search"></i>
            <input type="text" id="fleetSearch" placeholder="Search brand, model, registration number..." onkeyup="filterFleetGrid()">
        </div>

        <select class="fleet-filter" id="brandFilter" onchange="filterFleetGrid()">
            <option value="all">All Brands</option>
            <option value="BMW">BMW</option>
            <option value="Mercedes">Mercedes</option>
            <option value="Porsche">Porsche</option>
            <option value="Audi">Audi</option>
            <option value="Range Rover">Range Rover</option>
            <option value="Lamborghini">Lamborghini</option>
        </select>

        <select class="fleet-filter" id="typeFilter" onchange="filterFleetGrid()">
            <option value="all">All Categories</option>
            <option value="Sports">Sports</option>
            <option value="Grand Tourer">Grand Tourer</option>
            <option value="Supercar">Supercar</option>
            <option value="Luxury">Luxury</option>
            <option value="SUV">SUV</option>
        </select>

        <select class="fleet-filter" id="statusFilter" onchange="filterFleetGrid()">
            <option value="all">All Statuses</option>
            <option value="Available">Available</option>
            <option value="Rented">Rented</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Unavailable">Unavailable</option>
        </select>
    </section>

    <!-- VEHICLE GRID -->
    <section class="vehicle-grid" id="vehicleGrid">
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $car): ?>
                <div class="vehicle-card" 
                     data-brand="<?= htmlspecialchars($car['brand']) ?>"
                     data-type="<?= htmlspecialchars($car['category']) ?>"
                     data-status="<?= htmlspecialchars($car['status']) ?>"
                     data-search="<?= htmlspecialchars(strtolower($car['brand'] . ' ' . $car['model'] . ' ' . $car['registration_number'] . ' ' . $car['vehicle_code'])) ?>">
                    
                    <div class="vehicle-image">
                        <span class="vehicle-status <?= strtolower($car['status']) ?>-status">
                            <?= htmlspecialchars($car['status']) ?>
                        </span>
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                    </div>

                    <div class="vehicle-card-content p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="vehicle-category"><?= htmlspecialchars(strtoupper($car['category'])) ?></span>
                            <span class="text-muted small"><?= htmlspecialchars($car['registration_number']) ?></span>
                        </div>

                        <h4 class="h5 mb-2"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>

                        <div class="vehicle-specs d-flex gap-3 text-muted small mb-3">
                            <span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($car['year']) ?></span>
                            <span><i class="bi bi-gear-wide-connected me-1"></i><?= htmlspecialchars($car['transmission']) ?></span>
                            <span><i class="bi bi-fuel-pump me-1"></i><?= htmlspecialchars($car['fuel_type']) ?></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 border-secondary-subtle">
                            <div>
                                <strong class="text-gold fs-5">₹<?= number_format($car['price_per_day']) ?></strong>
                                <span class="text-muted small">/ day</span>
                            </div>

                            <div class="btn-group">
                                <button class="btn btn-outline-light btn-sm" onclick="viewVehicle(<?= $car['id'] ?>)" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="editVehicle(<?= $car['id'] ?>)" title="Edit Vehicle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteVehicle(<?= $car['id'] ?>, '<?= htmlspecialchars(addslashes($car['brand'] . ' ' . $car['model'])) ?>')" title="Delete Vehicle">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1 d-block mb-3 text-gold"></i>
                <p>No vehicles found in fleet repository.</p>
            </div>
        <?php endif; ?>
    </section>

</main>

<!-- ================= VEHICLE MODAL (ADD / EDIT) ================= -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="vehicleModalTitle">Add New Vehicle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="vehicleForm" onsubmit="handleSaveVehicle(event)">
        <div class="modal-body p-4">
            <input type="hidden" id="vId" name="id" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Brand *</label>
                    <input type="text" class="form-control rydex-input" id="vBrand" name="brand" placeholder="e.g. BMW" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Model *</label>
                    <input type="text" class="form-control rydex-input" id="vModel" name="model" placeholder="e.g. M4 Competition" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Variant</label>
                    <input type="text" class="form-control rydex-input" id="vVariant" name="variant" placeholder="e.g. xDrive Coupe">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Year *</label>
                    <input type="number" class="form-control rydex-input" id="vYear" name="year" value="<?= date('Y') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Category *</label>
                    <select class="form-select rydex-input" id="vCategory" name="category" required>
                        <option value="Sports">Sports</option>
                        <option value="Grand Tourer">Grand Tourer</option>
                        <option value="Supercar">Supercar</option>
                        <option value="Luxury">Luxury</option>
                        <option value="SUV">SUV</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Registration Number *</label>
                    <input type="text" class="form-control rydex-input" id="vRegNumber" name="registration_number" placeholder="e.g. MH-02-EQ-4400" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Price Per Day (₹) *</label>
                    <input type="number" step="0.01" class="form-control rydex-input" id="vPrice" name="price_per_day" placeholder="8500" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Status *</label>
                    <select class="form-select rydex-input" id="vStatus" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Rented">Rented</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Fuel Type</label>
                    <input type="text" class="form-control rydex-input" id="vFuel" name="fuel_type" value="Petrol">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Transmission</label>
                    <input type="text" class="form-control rydex-input" id="vTrans" name="transmission" value="Automatic">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Seats</label>
                    <input type="number" class="form-control rydex-input" id="vSeats" name="seats" value="4">
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted small">Image Path / URL</label>
                    <input type="text" class="form-control rydex-input" id="vImage" name="image" value="images/bmw-m4.png">
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted small">Description</label>
                    <textarea class="form-control rydex-input" id="vDesc" name="description" rows="3" placeholder="Brief vehicle specs and luxury description..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-top border-secondary">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="gold-btn">Save Vehicle</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= VIEW VEHICLE MODAL ================= -->
<div class="modal fade" id="viewVehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rydex-modal-content">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title font-cinzel text-gold" id="viewVehicleTitle">Vehicle Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewVehicleBody">
        <!-- Dynamically loaded -->
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
