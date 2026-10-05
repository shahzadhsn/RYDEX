# RYDEX – Ride. Rent. Repeat.
### Full-Stack Luxury Car Rental Management System (Admin & User Portals + GPS Tracking & License Verification)

---

## 🏎️ Project Overview

**RYDEX** is a complete full-stack luxury car rental web application equipped with an **Admin Operations Portal**, a **Customer Portal**, **GPS Live Satellite Tracking with Interactive Leaflet Maps**, and a **Driving License Verification Security System**. Built for XAMPP deployment with **PHP 8+**, **PDO Prepared Statements**, **MySQL**, and **Bootstrap 5**, RYDEX features dark luxury visual aesthetics with matte black and gold accents.

---

## ✨ Key Security & Operational Modules

### 📡 1. GPS Live Tracking & Telemetry System (`gps_tracking.php`, `api/gps_tracking.php`)
- **Interactive Satellite Map**: Built with **Leaflet.js** and CartoDB Dark Matter map layers.
- **Live Vehicle Markers**: Rented supercars pulse in Gold (`#c8a45d`), available cars in Green, and maintenance cars in Orange.
- **Telemetry Inspector**:
  - Live coordinates (Latitude / Longitude).
  - Current Vehicle Speed (km/h) & Speed Alerts.
  - Engine Status (Running, Idle, Off).
  - Fuel Level % indicator & Last Location Landmark (e.g. Bandra-Worli Sea Link, BKC).
- **Remote Engine Control**: One-click remote engine cut-off / lock trigger for admin security.

---

### 🪪 2. Driving License Verification System (`license_verification.php`, `user_license_verify.php`, `api/license_verification.php`)
- **Customer Verification Portal (`user_license_verify.php`)**:
  - Government Driving Permit submission portal.
  - License Number & Expiry Date validation.
  - Document image uploader for License Front and Back photos.
  - Verification badges (`Verified`, `Pending Review`, `Rejected`).
- **Admin Verification Queue (`license_verification.php`)**:
  - Verification queue dashboard displaying document submission count and status counters.
  - High-resolution **Document Photo Inspector Modal**.
  - One-click **Approve License** or **Reject License** (with custom rejection feedback).
- **Booking Security Guard**:
  - Automatic validation blocking unverified or rejected users from placing supercar reservations.

---

### 👤 3. Customer / User Portal
1. **Customer Registration & Login (`user_register.php`, `user_login.php`)**:
   - Secure customer account creation with driving license verification.
2. **Customer Dashboard (`user_dashboard.php`)**:
   - Client Code display (e.g. `RYX-C101`).
   - Active Reservation live card, trip stats, and lifetime spend.
3. **Interactive Fleet & Booking Engine (`user_fleet.php`)**:
   - Catalog filtering by brand & category (Sports, Supercar, Luxury, SUV, Grand Tourer).
   - "Book Now" modal with auto-calculated duration and total price breakdown.
4. **My Bookings (`user_bookings.php`)**:
   - Customer reservation ledger with Digital Receipt Inspector.
5. **My Profile (`user_profile.php`)**:
   - Update contact details, address, and account password.

---

### 🛡️ 4. Admin Operations Portal
1. **Admin Authentication (`login.php`, `logout.php`, `auth/`)**:
   - Session-guarded routes (`requireLogin()`).
2. **Operational Dashboard (`dashboard.php`)**:
   - Operational statistics, SVG revenue chart, fleet status doughnut.
3. **Fleet Management (`fleet.php`, `api/vehicles.php`)**:
   - Full vehicle repository management (Add, Edit, View Specs, Safe Deactivate/Delete).
4. **Booking Management (`bookings.php`, `api/bookings.php`)**:
   - **Bug #32 Fixed**: Dynamic modal rendering for each specific booking row.
5. **Customer Directory (`customers.php`, `api/customers.php`)**:
   - Client directory with full trip history inspector.
6. **Payments Ledger (`payments.php`, `api/payments.php`)**:
   - Financial ledger with instant status toggles.
7. **Analytics & Reports (`reports.php`, `api/reports.php`)**:
   - Date range filters and one-click **CSV Report Exporter**.
8. **System Settings (`settings.php`)**:
   - Admin credentials management and pricing configuration.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Vanilla Dark Luxury Theme), Bootstrap 5, Bootstrap Icons, Leaflet.js, JavaScript (Fetch API & Async/Await).
- **Backend**: PHP 8+ (Pure PDO, Prepared Statements, Session Security).
- **Database**: MySQL 8+ / MariaDB (`rydex_db`).
- **Server Environment**: XAMPP Apache & MySQL.

---

## 📂 Project Folder Structure

```
RYDEX/
├── index.php                 # Public Luxury Landing Page
│
├── gps_tracking.php          # Admin GPS Live Tracking & Map Page
├── license_verification.php  # Admin License Verification Queue Page
├── user_license_verify.php   # Customer License Upload Portal
│
├── user_login.php            # Customer Login Screen
├── user_register.php         # Customer Registration Screen
├── user_logout.php           # Customer Logout Handler
├── user_dashboard.php        # Customer Overview Dashboard
├── user_fleet.php            # Customer Vehicle Catalog & Booking Engine
├── user_bookings.php         # Customer Reservation History & Receipt Viewer
├── user_profile.php          # Customer Profile & License Settings
│
├── login.php                 # Admin Portal Login Screen
├── logout.php                # Admin Logout Handler
├── dashboard.php             # Admin Operational Dashboard
├── fleet.php                 # Admin Vehicle Management Module
├── bookings.php              # Admin Booking & Reservation Module
├── customers.php             # Admin Client Directory
├── payments.php              # Admin Financial Ledger
├── reports.php               # Admin Business Analytics & CSV Exporter
├── settings.php              # Admin System Settings
│
├── config/
│   └── database.php          # Singleton PDO MySQL Connection Helper
│
├── auth/
│   ├── auth_check.php        # Admin Session Guard Helper
│   ├── login_handler.php     # Admin Login API
│   ├── user_auth.php         # Customer Session Guard Helper
│   ├── user_login_handler.php # Customer Login API
│   └── user_register_handler.php # Customer Registration API
│
├── api/
│   ├── gps_tracking.php      # Live GPS Telemetry API
│   ├── license_verification.php # License Verification API
│   ├── dashboard.php         # Admin Stats & Chart Data Endpoint
│   ├── vehicles.php          # Vehicle CRUD & Filter API
│   ├── bookings.php         # Admin Booking API
│   ├── customers.php        # Customer Directory API
│   ├── payments.php         # Payments Ledger API
│   ├── reports.php          # Report Calculations & CSV Exporter
│   ├── user_bookings.php    # Customer Booking & Cancellation API
│   └── user_profile.php     # Customer Profile & Password API
│
├── includes/
│   ├── header.php           # Admin HTML Head Include
│   ├── sidebar.php          # Admin Static Non-Overlapping Sidebar
│   ├── topbar.php           # Admin Topbar Include
│   ├── footer.php           # Admin Footer & Toast Include
│   ├── user_header.php      # Customer Header Include
│   ├── user_navbar.php      # Customer Top Navbar Include
│   └── user_footer.php      # Customer Footer Include
│
├── css/
│   └── style.css            # Core RYDEX Dark Luxury CSS System
│
├── js/
│   └── script.js            # Fetch API, Dynamic Modals, Toast Notifications
│
├── images/                  # Automotive Assets, Maps & Sample Documents
│
├── database/
│   └── database.sql         # SQL Schema, Foreign Keys & Seed Data
│
└── README.md                # Project Documentation & Setup Guide
```

---

## 🚀 XAMPP Setup & Installation Guide

1. **Deploy Project Files**:
   - Copy the `rydex` folder into your XAMPP `htdocs` directory:
     `C:\xampp\htdocs\rydex`

2. **Import Database into phpMyAdmin**:
   - Open your browser: `http://localhost/phpmyadmin/`
   - Create a database named `rydex_db` (Collation: `utf8mb4_unicode_ci`).
   - Click **Import**, choose `C:\xampp\htdocs\rydex\database\database.sql`, and click **Go**.

3. **Launch URLs**:
   - **Public Website**: `http://localhost/rydex/index.php`
   - **Admin GPS Live Map**: `http://localhost/rydex/gps_tracking.php`
   - **Admin License Verification Queue**: `http://localhost/rydex/license_verification.php`
   - **Customer License Upload**: `http://localhost/rydex/user_license_verify.php`
   - **Customer Portal Login**: `http://localhost/rydex/user_login.php`
   - **Admin Portal Login**: `http://localhost/rydex/login.php`

---

## 🔐 Credentials for Testing

### 👤 Customer Account
- **Email**: `ahmed.khan@example.com`
- **Password**: `Password123!`

### 🛡️ Admin Account
- **Username**: `admin`
- **Password**: `Password123!`

---

### Brand Slogan: *RYDEX – Ride. Rent. Repeat.*
