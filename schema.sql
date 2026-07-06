CREATE DATABASE IF NOT EXISTS online_multi_doctor_appointment;
USE online_multi_doctor_appointment;

CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  specialty VARCHAR(120) NOT NULL,
  clinic VARCHAR(150) NOT NULL,
  working_hours VARCHAR(120) NOT NULL,
  status ENUM('Active', 'Inactive') DEFAULT 'Active'
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  time_slot VARCHAR(20) NOT NULL,
  reason TEXT,
  status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_doctor_slot (doctor_id, appointment_date, time_slot),
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

INSERT INTO doctors (full_name, specialty, clinic, working_hours) VALUES
('Dr. Aminah Tan', 'General Medicine', 'Sunway Clinic', 'Mon - Fri, 9:00 AM - 1:00 PM'),
('Dr. Raj Kumar', 'Cardiology', 'Heart Care Wing', 'Mon, Wed, Fri, 2:00 PM - 6:00 PM'),
('Dr. Wei Ling', 'Pediatrics', 'Children''s Clinic', 'Tue - Sat, 10:00 AM - 4:00 PM'),
('Dr. Farid Nordin', 'Dermatology', 'Skin & Wellness', 'Tue, Thu, Sat, 1:00 PM - 5:00 PM');
