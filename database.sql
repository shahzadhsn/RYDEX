-- RYDEX Database Schema & Demo Seed Data
-- Database: rydex_db

CREATE DATABASE IF NOT EXISTS `rydex_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rydex_db`;

-- Drop tables if exists (clean reset)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Admins Table
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` VARCHAR(20) DEFAULT 'Administrator',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin (Default credentials: admin / Password123!)
INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', 'Alex Hunter', 'admin@rydex.com', 'Administrator');

-- 2. Customers Table
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_code` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT,
  `city` VARCHAR(50) DEFAULT 'Mumbai',
  `state` VARCHAR(50) DEFAULT 'Maharashtra',
  `license_number` VARCHAR(50) NOT NULL,
  `license_expiry` DATE NOT NULL,
  `license_front_image` VARCHAR(255) DEFAULT 'images/license_sample.jpg',
  `license_back_image` VARCHAR(255) DEFAULT 'images/license_sample.jpg',
  `verification_status` ENUM('Verified', 'Pending', 'Rejected', 'Not Uploaded') DEFAULT 'Verified',
  `verification_notes` TEXT,
  `verified_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Customers (10 realistic fictional customers with default password 'Password123!')
INSERT INTO `customers` (`id`, `customer_code`, `full_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `license_number`, `license_expiry`, `verification_status`, `verification_notes`, `status`) VALUES
(1, 'RYX-C101', 'Ahmed Khan', 'ahmed.khan@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98201 12345', '12 Luxury Drive, Bandra West', 'Mumbai', 'Maharashtra', 'DL-MH02-2021-9988', '2028-12-31', 'Verified', 'Verified by Admin Alex Hunter on document scan.', 'Active'),
(2, 'RYX-C102', 'Rahul Sharma', 'rahul.sharma@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98765 43210', '45 Park Avenue, Colaba', 'Mumbai', 'Maharashtra', 'DL-MH01-2019-4433', '2027-08-15', 'Verified', 'Government DL record validated.', 'Active'),
(3, 'RYX-C103', 'Arjun Mehta', 'arjun.mehta@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98111 22334', '88 Marine Drive', 'Mumbai', 'Maharashtra', 'DL-MH01-2020-5566', '2029-05-20', 'Verified', 'Verified for supercar rental privileges.', 'Active'),
(4, 'RYX-C104', 'Sameer Ali', 'sameer.ali@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 97654 32109', '7 Worli Sea Face', 'Mumbai', 'Maharashtra', 'DL-MH02-2022-7711', '2030-01-10', 'Pending', 'License photo uploaded, pending admin review.', 'Active'),
(5, 'RYX-C105', 'Priya Kapoor', 'priya.kapoor@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 99300 44556', '102 Juhu Scheme', 'Mumbai', 'Maharashtra', 'DL-MH03-2021-1234', '2028-11-25', 'Verified', 'Identity & license verified.', 'Active'),
(6, 'RYX-C106', 'Vikramaditya Singhania', 'vikram.s@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98210 99887', 'Altamount Road Mansions', 'Mumbai', 'Maharashtra', 'DL-MH01-2018-0099', '2027-04-18', 'Verified', 'VIP Client verification cleared.', 'Active'),
(7, 'RYX-C107', 'Ananya Roy', 'ananya.roy@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98450 33221', '14 Indiranagar 100ft Rd', 'Bengaluru', 'Karnataka', 'DL-KA01-2020-6644', '2029-09-09', 'Pending', 'Awaiting document upload confirmation.', 'Active'),
(8, 'RYX-C108', 'Rohan Verma', 'rohan.verma@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 99887 76655', '55 Jubilee Hills', 'Hyderabad', 'Telangana', 'DL-TS09-2021-8822', '2028-03-30', 'Verified', 'Interstate driving permit verified.', 'Active'),
(9, 'RYX-C109', 'Kavita Reddy', 'kavita.reddy@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 97112 33445', '22 Boat Club Road', 'Pune', 'Maharashtra', 'DL-MH12-2022-3311', '2030-07-14', 'Rejected', 'License photo blurry, requested clear re-upload.', 'Active'),
(10, 'RYX-C110', 'Devansh Malhotra', 'devansh.m@example.com', '$2y$10$wT0q5sN9X3LhR.z8M4P.u.8X9g8xZ7W6v5U4t3S2r1Q0P9O8N7M6L', '+91 98990 11223', '90 Golf Course Road', 'Gurugram', 'Haryana', 'DL-HR26-2020-5599', '2029-10-05', 'Verified', 'Verified.', 'Active');

-- 3. Vehicles Table
CREATE TABLE `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vehicle_code` VARCHAR(20) NOT NULL UNIQUE,
  `brand` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `variant` VARCHAR(50) DEFAULT '',
  `year` INT NOT NULL,
  `category` ENUM('Sports', 'Luxury', 'SUV', 'Supercar', 'Grand Tourer') NOT NULL,
  `registration_number` VARCHAR(30) NOT NULL UNIQUE,
  `price_per_day` DECIMAL(10,2) NOT NULL,
  `fuel_type` VARCHAR(20) DEFAULT 'Petrol',
  `transmission` VARCHAR(20) DEFAULT 'Automatic',
  `seats` INT DEFAULT 2,
  `mileage` VARCHAR(30) DEFAULT '12 km/l',
  `status` ENUM('Available', 'Rented', 'Maintenance', 'Unavailable') DEFAULT 'Available',
  `image` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `latitude` DECIMAL(10,7) DEFAULT 19.0760000,
  `longitude` DECIMAL(10,7) DEFAULT 72.8777000,
  `current_speed` INT DEFAULT 0,
  `heading` INT DEFAULT 90,
  `fuel_level` INT DEFAULT 85,
  `engine_status` ENUM('Running', 'Idle', 'Off') DEFAULT 'Off',
  `last_location_name` VARCHAR(255) DEFAULT 'Bandra West, Mumbai',
  `gps_device_id` VARCHAR(50) DEFAULT 'GPS-RYX-001',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Vehicles (10 high-end luxury cars with realistic GPS telemetry in Mumbai)
INSERT INTO `vehicles` (`id`, `vehicle_code`, `brand`, `model`, `variant`, `year`, `category`, `registration_number`, `price_per_day`, `fuel_type`, `transmission`, `seats`, `mileage`, `status`, `image`, `description`, `latitude`, `longitude`, `current_speed`, `heading`, `fuel_level`, `engine_status`, `last_location_name`, `gps_device_id`) VALUES
(1, 'RYX-V001', 'BMW', 'M4 Competition', 'xDrive Coupe', 2024, 'Sports', 'MH-02-EQ-4400', 8500.00, 'Petrol', 'Automatic', 4, '10 km/l', 'Available', 'images/bmw-m4.png', 'Iconic TwinPower Turbo inline 6 cylinder engine.', 19.0596000, 72.8295000, 0, 45, 92, 'Off', 'Bandra West Hub, Mumbai', 'GPS-RYX-9901'),
(2, 'RYX-V002', 'Mercedes', 'AMG GT', '63 S 4-Door', 2023, 'Grand Tourer', 'MH-01-GT-9000', 12000.00, 'Petrol', 'Automatic', 4, '8 km/l', 'Rented', 'images/mercedes-amg.png', 'Handcrafted AMG 4.0L V8 Biturbo performance.', 19.0330000, 72.8185000, 68, 180, 74, 'Running', 'Bandra-Worli Sea Link, Mumbai', 'GPS-RYX-9902'),
(3, 'RYX-V003', 'Porsche', '911 Turbo S', 'Coupe', 2024, 'Supercar', 'MH-02-PR-9110', 15000.00, 'Petrol', 'Automatic', 2, '9 km/l', 'Available', 'images/porsche-911.png', 'Benchmark super sports car engineering.', 18.9438000, 72.8231000, 0, 90, 88, 'Off', 'Marine Drive Promenade, Mumbai', 'GPS-RYX-9903'),
(4, 'RYX-V004', 'Audi', 'RS7 Sportback', 'Performance', 2023, 'Luxury', 'MH-01-RS-7000', 11000.00, 'Petrol', 'Automatic', 5, '9.5 km/l', 'Available', 'images/audi-rs7.png', 'Brutal twin-turbo V8 executive sportback.', 19.0657000, 72.8686000, 0, 0, 95, 'Off', 'BKC Financial Hub, Mumbai', 'GPS-RYX-9904'),
(5, 'RYX-V005', 'Range Rover', 'Sport SV', 'Edition One', 2024, 'SUV', 'MH-02-RR-0001', 14000.00, 'Petrol', 'Automatic', 5, '7.5 km/l', 'Rented', 'images/range-rover.png', 'Supreme all-terrain luxury capability.', 19.1000000, 72.8250000, 52, 270, 61, 'Running', 'Juhu Tara Road, Mumbai', 'GPS-RYX-9905'),
(6, 'RYX-V006', 'Mercedes', 'G 63 AMG', 'Grand Edition', 2024, 'SUV', 'MH-01-G-6300', 18000.00, 'Petrol', 'Automatic', 5, '6.5 km/l', 'Maintenance', 'images/g-wagon.png', 'The timeless G-Class silhouette engineered with V8 power.', 19.0880000, 72.8360000, 0, 135, 45, 'Idle', 'RYDEX Service Center, Kurla West', 'GPS-RYX-9906'),
(7, 'RYX-V007', 'BMW', '7 Series 740i', 'M Sport', 2024, 'Luxury', 'MH-02-BM-7700', 13000.00, 'Petrol', 'Automatic', 5, '11 km/l', 'Available', 'images/bmw-7series.png', 'Ultra-luxury flagship sedan.', 19.0896000, 72.8656000, 0, 90, 100, 'Off', 'Chhatrapati Shivaji Airport T2, Mumbai', 'GPS-RYX-9907'),
(8, 'RYX-V008', 'Porsche', 'Cayenne GTS', 'Coupe', 2023, 'SUV', 'MH-01-CY-5500', 10500.00, 'Petrol', 'Automatic', 5, '8.5 km/l', 'Available', 'images/porsche-cayenne.png', 'V8 twin-turbo sport SUV tuned for enthusiast drivers.', 18.9750000, 72.8250000, 0, 315, 82, 'Off', 'Worli Sea Face, Mumbai', 'GPS-RYX-9908'),
(9, 'RYX-V009', 'Audi', 'R8 V10 Performance', 'Decennium', 2023, 'Supercar', 'MH-02-R8-1000', 17000.00, 'Petrol', 'Automatic', 2, '7 km/l', 'Available', 'images/audi-r8.png', 'Naturally aspirated 5.2L V10 screaming to 8,700 RPM.', 18.9220000, 72.8310000, 0, 45, 90, 'Off', 'Colaba Causeway, Mumbai', 'GPS-RYX-9909'),
(10, 'RYX-V010', 'Lamborghini', 'Huracan EVO', 'Spyder', 2023, 'Supercar', 'MH-01-LH-0007', 25000.00, 'Petrol', 'Automatic', 2, '6 km/l', 'Rented', 'images/lamborghini.png', 'Unfiltered Italian supercar passion.', 19.0750000, 72.8770000, 84, 110, 58, 'Running', 'Eastern Express Highway, Mumbai', 'GPS-RYX-9910');

-- 4. Bookings Table
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(20) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `vehicle_id` INT NOT NULL,
  `pickup_date` DATE NOT NULL,
  `return_date` DATE NOT NULL,
  `rental_days` INT NOT NULL,
  `price_per_day` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('Cash', 'UPI', 'Credit Card', 'Debit Card', 'Bank Transfer') DEFAULT 'Credit Card',
  `payment_status` ENUM('Pending', 'Paid', 'Partial', 'Refunded') DEFAULT 'Pending',
  `booking_status` ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Bookings (15 realistic bookings)
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `vehicle_id`, `pickup_date`, `return_date`, `rental_days`, `price_per_day`, `total_amount`, `payment_method`, `payment_status`, `booking_status`, `notes`) VALUES
(1, 'RYX1024', 1, 1, '2026-08-20', '2026-08-23', 3, 8500.00, 25500.00, 'Credit Card', 'Paid', 'Confirmed', 'Early morning airport delivery.'),
(2, 'RYX1025', 2, 2, '2026-08-22', '2026-08-26', 4, 12000.00, 48000.00, 'UPI', 'Pending', 'Confirmed', 'GPS tracking active.'),
(3, 'RYX1026', 3, 3, '2026-08-15', '2026-08-18', 3, 15000.00, 45000.00, 'Bank Transfer', 'Paid', 'Completed', 'Vehicle returned full tank.'),
(4, 'RYX1027', 4, 1, '2026-08-12', '2026-08-15', 3, 8500.00, 25500.00, 'Credit Card', 'Refunded', 'Cancelled', 'Travel plans changed.'),
(5, 'RYX1028', 5, 5, '2026-09-01', '2026-09-07', 6, 14000.00, 84000.00, 'Credit Card', 'Paid', 'Confirmed', 'Interstate Goa permit.'),
(6, 'RYX1029', 6, 10, '2026-09-10', '2026-09-12', 2, 25000.00, 50000.00, 'Bank Transfer', 'Paid', 'Confirmed', 'VIP Security tracking.'),
(7, 'RYX1030', 7, 4, '2026-09-14', '2026-09-17', 3, 11000.00, 33000.00, 'UPI', 'Paid', 'Completed', 'Completed drive.'),
(8, 'RYX1031', 8, 7, '2026-09-15', '2026-09-18', 3, 13000.00, 39000.00, 'Credit Card', 'Paid', 'Confirmed', 'Executive chauffeur package.'),
(9, 'RYX1032', 9, 8, '2026-09-05', '2026-09-08', 3, 10500.00, 31500.00, 'Debit Card', 'Paid', 'Completed', 'Returned on time.'),
(10, 'RYX1033', 10, 9, '2026-09-18', '2026-09-20', 2, 17000.00, 34000.00, 'Credit Card', 'Pending', 'Pending', 'Pending verification deposit.'),
(11, 'RYX1034', 2, 6, '2026-07-10', '2026-07-15', 5, 18000.00, 90000.00, 'Bank Transfer', 'Paid', 'Completed', 'Film shoot rental.'),
(12, 'RYX1035', 3, 2, '2026-09-21', '2026-09-24', 3, 12000.00, 36000.00, 'UPI', 'Paid', 'Confirmed', 'Repeat client.'),
(13, 'RYX1036', 5, 3, '2026-07-01', '2026-07-03', 2, 15000.00, 30000.00, 'Credit Card', 'Paid', 'Completed', 'Weekend drive.'),
(14, 'RYX1037', 7, 1, '2026-09-25', '2026-09-28', 3, 8500.00, 25500.00, 'UPI', 'Pending', 'Pending', 'Reserved online.'),
(15, 'RYX1038', 1, 4, '2026-06-10', '2026-06-12', 2, 11000.00, 22000.00, 'Cash', 'Paid', 'Completed', 'Corporate event.');

-- 5. Payments Table
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `payment_code` VARCHAR(20) NOT NULL UNIQUE,
  `booking_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('Cash', 'UPI', 'Credit Card', 'Debit Card', 'Bank Transfer') NOT NULL,
  `transaction_id` VARCHAR(50) NOT NULL,
  `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `payment_status` ENUM('Pending', 'Paid', 'Partial', 'Refunded', 'Failed') DEFAULT 'Paid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payments` (`id`, `payment_code`, `booking_id`, `customer_id`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `payment_status`) VALUES
(1, 'PAY-1001', 1, 1, 25500.00, 'Credit Card', 'TXN_9988771122', '2026-08-20 09:30:00', 'Paid'),
(2, 'PAY-1002', 3, 3, 45000.00, 'Bank Transfer', 'TXN_5544332211', '2026-08-15 11:15:00', 'Paid'),
(3, 'PAY-1003', 4, 4, 25500.00, 'Credit Card', 'TXN_REF_001122', '2026-08-12 14:00:00', 'Refunded'),
(4, 'PAY-1004', 5, 5, 84000.00, 'Credit Card', 'TXN_7788990011', '2026-09-01 10:45:00', 'Paid'),
(5, 'PAY-1005', 6, 6, 50000.00, 'Bank Transfer', 'TXN_6655443322', '2026-09-10 16:20:00', 'Paid'),
(6, 'PAY-1006', 7, 7, 33000.00, 'UPI', 'TXN_UPI_88776655', '2026-09-14 12:00:00', 'Paid'),
(7, 'PAY-1007', 8, 8, 39000.00, 'Credit Card', 'TXN_3322114455', '2026-09-15 15:10:00', 'Paid'),
(8, 'PAY-1008', 9, 9, 31500.00, 'Debit Card', 'TXN_1122334455', '2026-09-05 13:40:00', 'Paid'),
(9, 'PAY-1009', 11, 2, 90000.00, 'Bank Transfer', 'TXN_4455667788', '2026-07-10 10:00:00', 'Paid'),
(10, 'PAY-1010', 12, 3, 36000.00, 'UPI', 'TXN_UPI_11223344', '2026-09-21 09:00:00', 'Paid');

-- 6. Settings Table
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'RYDEX Luxury Car Rental'),
('company_email', 'contact@rydex.com'),
('company_phone', '+91 1800 793 3900'),
('company_address', '100 Luxury Boulevard, Bandra Kurla Complex, Mumbai, Maharashtra 400051'),
('currency_symbol', '₹'),
('default_tax_rate', '18'),
('security_deposit', '20000');

-- 7. Activity Logs Table
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT 1,
  `action` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `activity_logs` (`action`) VALUES
('System initialized with luxury fleet & GPS telemetry seed data.'),
('Admin Alex Hunter logged in.'),
('GPS Tracking initiated for Mercedes AMG GT (#MH-01-GT-9000).');
