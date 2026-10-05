<?php
$pageTitle = 'GPS Live Tracking';
$currentPage = 'gps';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$gpsVehicles = [];

if ($db) {
    try {
        $stmt = $db->query("
            SELECT v.id, v.vehicle_code, v.brand, v.model, v.registration_number, v.status, v.category, v.image, v.price_per_day,
                   COALESCE(v.latitude, 19.0760) as latitude,
                   COALESCE(v.longitude, 72.8777) as longitude,
                   COALESCE(v.current_speed, 0) as current_speed,
                   COALESCE(v.heading, 90) as heading,
                   COALESCE(v.fuel_level, 85) as fuel_level,
                   COALESCE(v.engine_status, 'Off') as engine_status,
                   COALESCE(v.last_location_name, 'Mumbai, Maharashtra') as last_location_name,
                   COALESCE(v.gps_device_id, CONCAT('GPS-RYX-', v.id)) as gps_device_id,
                   c.full_name as customer_name, c.phone as customer_phone, b.booking_code, b.pickup_date, b.return_date
            FROM vehicles v
            LEFT JOIN bookings b ON v.id = b.vehicle_id AND b.booking_status = 'Confirmed'
            LEFT JOIN customers c ON b.customer_id = c.id
            ORDER BY v.status DESC, v.id ASC
        ");
        $gpsVehicles = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #gpsMap {
        height: 560px;
        width: 100%;
        border-radius: 4px;
        border: 1px solid rgba(200, 164, 93, 0.25);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7);
        z-index: 1;
    }

    .telemetry-card {
        background: #151515;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 4px;
        padding: 14px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .telemetry-card:hover, .telemetry-card.selected {
        border-color: #c8a45d;
        background: rgba(200, 164, 93, 0.08);
    }

    .leaflet-popup-content-wrapper {
        background: #151515 !important;
        color: #ffffff !important;
        border: 1px solid #c8a45d !important;
        border-radius: 4px !important;
        font-family: 'Poppins', sans-serif !important;
    }

    .leaflet-popup-tip {
        background: #151515 !important;
    }

    .custom-car-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        color: #080808;
        font-weight: bold;
        box-shadow: 0 0 15px rgba(200, 164, 93, 0.6);
        border: 2px solid #ffffff;
    }

    .custom-car-marker.rented {
        background: #c8a45d;
        animation: pulseGold 2s infinite;
    }

    .custom-car-marker.available {
        background: #28a745;
    }

    .custom-car-marker.maintenance {
        background: #fd7e14;
    }

    @keyframes pulseGold {
        0% { box-shadow: 0 0 0 0 rgba(200, 164, 93, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(200, 164, 93, 0); }
        100% { box-shadow: 0 0 0 0 rgba(200, 164, 93, 0); }
    }
</style>

<!-- ================= MAIN CONTENT ================= -->
<main class="dashboard-content gps-main">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <p class="dashboard-greeting">FLEET SECURITY & TELEMETRY</p>
            <h1>GPS Live Tracking</h1>
            <p class="page-description text-muted">Real-time satellite coordinates, speed monitoring, and engine security control.</p>
        </div>

        <div class="d-flex gap-2">
            <button class="gold-btn btn-sm" onclick="refreshGpsData()">
                <i class="bi bi-arrow-repeat me-1"></i> Refresh Live Telemetry
            </button>
        </div>
    </div>

    <!-- MAIN GPS GRID -->
    <div class="row g-4">
        <!-- Interactive Map -->
        <div class="col-lg-8">
            <div class="dashboard-panel p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="panel-label">SATELLITE POSITIONING</span>
                        <h4 class="font-cinzel text-white mb-0">Live Map View</h4>
                    </div>

                    <div class="d-flex gap-2">
                        <span class="badge bg-gold text-dark"><i class="bi bi-broadcast me-1"></i> 10 GPS Units Connected</span>
                    </div>
                </div>

                <div id="gpsMap"></div>
            </div>
        </div>

        <!-- Telemetry Sidebar -->
        <div class="col-lg-4">
            <div class="dashboard-panel h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
                    <div>
                        <span class="panel-label">FLEET TELEMETRY</span>
                        <h4 class="font-cinzel text-gold mb-0">Vehicle List</h4>
                    </div>

                    <select class="form-select form-select-sm rydex-input w-auto" id="gpsFilter" onchange="renderVehicleCards()">
                        <option value="all">All Statuses</option>
                        <option value="Rented">Rented Out</option>
                        <option value="Available">Available</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>

                <div id="telemetryList" style="max-height: 480px; overflow-y: auto;">
                    <!-- Dynamically loaded vehicle cards -->
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map, markersGroup;
let vehiclesData = <?= json_encode($gpsVehicles) ?>;
let markersMap = {};

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    renderVehicleCards();
});

function initMap() {
    // Center on Mumbai coordinates
    map = L.map('gpsMap').setView([19.0760, 72.8777], 11);

    // OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    markersGroup = L.layerGroup().addTo(map);
    plotMarkers();
}

function plotMarkers() {
    markersGroup.clearLayers();
    markersMap = {};

    vehiclesData.forEach(v => {
        const lat = parseFloat(v.latitude);
        const lng = parseFloat(v.longitude);

        if (isNaN(lat) || isNaN(lng)) return;

        const statusClass = (v.status || '').toLowerCase();
        const iconHtml = `<div class="custom-car-marker ${statusClass}"><i class="bi bi-car-front-fill"></i></div>`;

        const customIcon = L.divIcon({
            html: iconHtml,
            className: '',
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        const popupContent = `
            <div style="min-width: 220px;" class="p-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-gold font-cinzel">${v.brand} ${v.model}</strong>
                    <span class="badge ${v.status === 'Rented' ? 'bg-gold text-dark' : 'bg-success'}">${v.status}</span>
                </div>
                <p class="small text-muted mb-1">Reg: <strong>${v.registration_number}</strong></p>
                <p class="small text-muted mb-1"><i class="bi bi-geo-alt text-gold me-1"></i>${v.last_location_name}</p>
                ${v.customer_name ? `<p class="small text-white mb-1">Renter: <strong>${v.customer_name}</strong></p>` : ''}
                
                <div class="row g-1 text-center my-2 py-2 border-top border-bottom border-secondary">
                    <div class="col-4">
                        <small class="text-muted d-block">Speed</small>
                        <strong class="text-gold">${v.current_speed} km/h</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Engine</small>
                        <strong class="${v.engine_status === 'Running' ? 'text-success' : 'text-danger'}">${v.engine_status}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Fuel</small>
                        <strong class="text-info">${v.fuel_level}%</strong>
                    </div>
                </div>

                <div class="mt-2 text-center">
                    <button class="btn ${v.engine_status === 'Off' ? 'btn-success' : 'btn-danger'} btn-sm w-100 py-1" onclick="toggleEngineRemote(${v.id}, '${v.engine_status === 'Off' ? 'Running' : 'Off'}')">
                        <i class="bi bi-power me-1"></i> ${v.engine_status === 'Off' ? 'Enable Engine' : 'Remote Engine Cut-Off'}
                    </button>
                </div>
            </div>
        `;

        const marker = L.marker([lat, lng], { icon: customIcon }).bindPopup(popupContent);
        markersGroup.addLayer(marker);
        markersMap[v.id] = marker;
    });
}

function renderVehicleCards() {
    const filter = document.getElementById('gpsFilter').value;
    const container = document.getElementById('telemetryList');
    container.innerHTML = '';

    const filtered = vehiclesData.filter(v => filter === 'all' || v.status === filter);

    if (filtered.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4 small">No vehicles match filter.</div>';
        return;
    }

    filtered.forEach(v => {
        const card = document.createElement('div');
        card.className = 'telemetry-card';
        card.onclick = () => focusVehicleOnMap(v.id);

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong class="text-white">${v.brand} ${v.model}</strong>
                <span class="booking-status ${v.status.toLowerCase()}">${v.status}</span>
            </div>
            <div class="text-muted small mb-2">${v.registration_number} | Device: ${v.gps_device_id}</div>
            
            <div class="d-flex justify-content-between align-items-center text-muted small">
                <span><i class="bi bi-speedometer2 text-gold me-1"></i>${v.current_speed} km/h</span>
                <span><i class="bi bi-power ${v.engine_status === 'Running' ? 'text-success' : 'text-muted'} me-1"></i>${v.engine_status}</span>
                <span><i class="bi bi-fuel-pump text-info me-1"></i>${v.fuel_level}%</span>
            </div>
            
            <div class="mt-2 text-muted extra-small text-truncate" style="font-size: 11px;">
                <i class="bi bi-geo-alt text-gold me-1"></i>${v.last_location_name}
            </div>
        `;
        container.appendChild(card);
    });
}

function focusVehicleOnMap(id) {
    const v = vehiclesData.find(x => x.id == id);
    if (!v) return;

    const lat = parseFloat(v.latitude);
    const lng = parseFloat(v.longitude);

    if (!isNaN(lat) && !isNaN(lng)) {
        map.setView([lat, lng], 15, { animate: true });
        if (markersMap[id]) {
            markersMap[id].openPopup();
        }
    }
}

async function toggleEngineRemote(id, newStatus) {
    if (!confirm(`Confirm remote engine command: set status to ${newStatus}?`)) return;

    try {
        const res = await fetch('api/gps_tracking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_engine', id: id, engine_status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            showRydexToast(data.message, 'success');
            refreshGpsData();
        } else {
            showRydexToast(data.message || 'Action failed.', 'error');
        }
    } catch (err) {
        showRydexToast('Network request failed.', 'error');
    }
}

async function refreshGpsData() {
    try {
        const res = await fetch('api/gps_tracking.php');
        const data = await res.json();
        if (data.success && data.data) {
            vehiclesData = data.data;
            plotMarkers();
            renderVehicleCards();
            showRydexToast('Live GPS coordinates updated.', 'info');
        }
    } catch (err) {
        showRydexToast('Could not update live telemetry.', 'error');
    }
}

// Auto-refresh GPS telemetry every 15 seconds
setInterval(refreshGpsData, 15000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
