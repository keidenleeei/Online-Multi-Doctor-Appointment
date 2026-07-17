-- Online Multi Doctor Appointment System
-- Database Schema Setup

CREATE DATABASE IF NOT EXISTS `doctor_appointmentbrid` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `doctor_appointmentbrid`;

-- 1. Users Table (Patients, Doctors, Admins)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Supports secure bcrypt hashing
  `phone` VARCHAR(20) NOT NULL,
  `role` ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Doctors Table
CREATE TABLE IF NOT EXISTS `doctors` (
  `doctor_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `specialization` VARCHAR(100) NOT NULL,
  `experience` INT NOT NULL,
  `consultation_fee` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Schedules Table
CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` INT AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT NOT NULL,
  `available_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Appointments Table
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `doctor_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  `appointment_date` DATE NOT NULL,
  `status` ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default Admin user if not exists
-- Hash for 'admin123'
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`)
SELECT 'System Admin', 'admin@omd.com', '$2y$10$vN0v7gIq9K0BWh0h0p9xCeL3k8xU3R6j6/B1v2iN2oO8m9v0v1y2K', '012-3456789', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'admin@omd.com');
