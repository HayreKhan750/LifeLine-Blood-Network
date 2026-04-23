-- Community Blood Donor & Emergency Matching System Schema
-- Run this in MySQL (e.g., phpMyAdmin) before using the application

CREATE DATABASE IF NOT EXISTS blood_donor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blood_donor_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('donor','hospital','admin') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donor_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50) DEFAULT 'India',
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    date_of_birth DATE,
    gender ENUM('male','female','other'),
    last_donation_date DATE,
    is_available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hospital_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    hospital_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50) DEFAULT 'India',
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    license_number VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    patient_blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_needed INT DEFAULT 1,
    urgency ENUM('normal','urgent','critical') DEFAULT 'normal',
    status ENUM('open','fulfilled','cancelled') DEFAULT 'open',
    required_date DATE,
    city VARCHAR(50),
    state VARCHAR(50),
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    hospital_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donor_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    donor_id INT NOT NULL,
    status ENUM('pending','contacted','confirmed','declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    request_id INT NOT NULL,
    hospital_id INT NOT NULL,
    donation_date DATE NOT NULL,
    units_donated INT DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_donor (donor_id),
    INDEX idx_donation_date (donation_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Insert default admin (CHANGE THIS PASSWORD IN PRODUCTION)
-- Generate a secure password hash and update this before deploying
INSERT INTO users (email, password, role, is_active) VALUES
('admin@bloodsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- Default password: 'SecureAdmin2024!' - CHANGE IMMEDIATELY AFTER FIRST LOGIN
-- Use: password_hash('your_new_password', PASSWORD_DEFAULT) to generate

CREATE TABLE IF NOT EXISTS blood_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    operating_hours VARCHAR(100),
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO blood_banks (name, address, city, state, phone, email, operating_hours) VALUES
('Indian Red Cross Blood Bank', '1 Red Cross Marg, Near Connaught Place', 'New Delhi', 'Delhi', '+91-11-23716441', 'delhi@redcross.org', '24/7'),
('Rotary TTK Blood Bank', '737 Anna Salai, Thousand Lights', 'Chennai', 'Tamil Nadu', '+91-44-28271333', 'info@rotaryttk.org', '8AM-8PM'),
('Prathama Blood Centre', 'Opposite Old Civil Hospital, Asarwa', 'Ahmedabad', 'Gujarat', '+91-79-22681234', 'info@prathama.org', '24/7'),
('Sankalp India Foundation', 'CA-37, 17th Cross, Malleshwaram', 'Bangalore', 'Karnataka', '+91-80-4121-0000', 'contact@sankalpindia.net', '9AM-6PM'),
('Lions Blood Bank', '315, Dr. Annie Besant Road, Worli', 'Mumbai', 'Maharashtra', '+91-22-2493-5555', 'mumbai@lionsbloodbank.org', '8AM-10PM'),
('Kolkata Blood Bank', '5 AJC Bose Road, Near Park Street', 'Kolkata', 'West Bengal', '+91-33-2225-4444', 'info@kolkatabloodbank.org', '24/7'),
('Hyderabad Blood Centre', '6-1-1 Padmarao Nagar, Secunderabad', 'Hyderabad', 'Telangana', '+91-40-2781-2222', 'info@hydbloodcentre.org', '9AM-9PM'),
('Jeevan Blood Bank', '72 Marshalls Road, Egmore', 'Chennai', 'Tamil Nadu', '+91-44-2827-3333', 'info@jeevan.org', '24/7');

-- Performance Indexes for production use
CREATE INDEX idx_donor_blood_type ON donor_profiles(blood_type);
CREATE INDEX idx_donor_city ON donor_profiles(city);
CREATE INDEX idx_donor_state ON donor_profiles(state);
CREATE INDEX idx_donor_available ON donor_profiles(is_available);
CREATE INDEX idx_request_status ON blood_requests(status);
CREATE INDEX idx_request_urgency ON blood_requests(urgency);
CREATE INDEX idx_request_blood_type ON blood_requests(patient_blood_type);
CREATE INDEX idx_request_city ON blood_requests(city);
CREATE INDEX idx_matches_request ON donor_matches(request_id);
CREATE INDEX idx_matches_donor ON donor_matches(donor_id);
CREATE INDEX idx_matches_status ON donor_matches(status);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);
