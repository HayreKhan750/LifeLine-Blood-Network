-- LifeLine Blood Network - Advanced Schema Upgrade
-- Run this after the base setup.sql to add new advanced features

USE blood_donor_db;

-- Notifications system
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('request_match','donation_confirmed','achievement','system','message','urgent_request') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Donor achievements and badges
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    badge_type ENUM('first_donation','lifesaver_bronze','lifesaver_silver','lifesaver_gold','platinum_hero','emergency_responder','regular_donor','community_champion','universal_donor') NOT NULL,
    badge_name VARCHAR(100) NOT NULL,
    badge_description TEXT,
    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_badge (donor_id, badge_type),
    INDEX idx_donor (donor_id)
) ENGINE=InnoDB;

-- Testimonials and success stories
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    hospital_id INT,
    recipient_name VARCHAR(100),
    story TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_approved (is_approved, created_at)
) ENGINE=InnoDB;

-- Hospital blood inventory tracking
CREATE TABLE IF NOT EXISTS blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_available INT DEFAULT 0,
    units_needed INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hospital_blood (hospital_id, blood_type),
    INDEX idx_hospital (hospital_id)
) ENGINE=InnoDB;

-- Messages between donors and hospitals
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_receiver (receiver_id, is_read),
    INDEX idx_conversation (sender_id, receiver_id)
) ENGINE=InnoDB;

-- Add donor columns (check if column exists first)
ALTER TABLE donor_profiles ADD COLUMN is_verified TINYINT(1) DEFAULT 0;
ALTER TABLE donor_profiles ADD COLUMN total_donations INT DEFAULT 0;
ALTER TABLE donor_profiles ADD COLUMN donation_points INT DEFAULT 0;
ALTER TABLE donor_profiles ADD COLUMN next_eligible_date DATE NULL;
ALTER TABLE donor_profiles ADD COLUMN tier ENUM('bronze','silver','gold','platinum','diamond') DEFAULT 'bronze';

-- Add request columns
ALTER TABLE blood_requests ADD COLUMN contact_person VARCHAR(100);
ALTER TABLE blood_requests ADD COLUMN contact_phone VARCHAR(20);

-- Add donation verification columns
ALTER TABLE donation_history ADD COLUMN verified_by INT NULL;
ALTER TABLE donation_history ADD COLUMN verification_date TIMESTAMP NULL;
ALTER TABLE donation_history ADD COLUMN certificate_issued TINYINT(1) DEFAULT 0;

-- Add indexes for performance
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_achievements_type ON achievements(badge_type);
CREATE INDEX idx_inventory_blood ON blood_inventory(blood_type);

-- Insert default testimonials for display
INSERT INTO testimonials (donor_id, story, rating, is_approved) VALUES
(NULL, 'LifeLine connected me with a donor in just 30 minutes during a critical emergency. The system works flawlessly and the donor arrived at our hospital within the hour. Truly lifesaving technology.', 5, 1),
(NULL, 'I have been a regular donor for 2 years through LifeLine. The platform makes it so easy to help people in need. I get notifications when someone nearby needs my blood type and can respond immediately.', 5, 1),
(NULL, 'As a hospital administrator, LifeLine has reduced our blood shortage incidents by 80%. The matching algorithm and emergency SOS feature are game changers for healthcare.', 5, 1);
