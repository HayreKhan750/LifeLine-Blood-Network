-- Sample data for LifeLine Blood Network
-- This populates the database with demo content for the PHP version

-- First, create sample users (passwords are 'password123' hashed)
INSERT INTO users (email, password, role, is_active) VALUES
('rahul.sharma@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 1),
('priya.patel@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 1),
('amit.kumar@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 1),
('sneha.gupta@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 1),
('vikram.rao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 1),
('city.hospital@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hospital', 1),
('apollo.centre@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hospital', 1),
('st.johns@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hospital', 1);

-- Insert donor profiles
INSERT INTO donor_profiles (user_id, full_name, blood_type, phone, date_of_birth, gender, address, city, state, country, latitude, longitude, is_available, is_verified, total_donations, donation_points, tier, last_donation_date, next_eligible_date, created_at) VALUES
(1, 'Rahul Sharma', 'O+', '+91-9876543210', '1990-05-15', 'male', '123 Marine Lines', 'Mumbai', 'Maharashtra', 'India', 19.0760, 72.8777, 1, 1, 24, 2400, 'gold', '2024-12-01', '2025-03-01', NOW()),
(2, 'Priya Patel', 'A+', '+91-9876543211', '1988-08-22', 'female', '456 Connaught Place', 'Delhi', 'NCR', 'India', 28.6139, 77.2090, 1, 1, 18, 1800, 'silver', '2024-11-15', '2025-02-15', NOW()),
(3, 'Amit Kumar', 'B+', '+91-9876543212', '1992-03-10', 'male', '789 Koramangala', 'Bangalore', 'Karnataka', 'India', 12.9716, 77.5946, 1, 1, 15, 1500, 'silver', '2024-12-20', '2025-03-20', NOW()),
(4, 'Sneha Gupta', 'AB+', '+91-9876543213', '1995-11-05', 'female', '321 Anna Nagar', 'Chennai', 'Tamil Nadu', 'India', 13.0827, 80.2707, 1, 0, 12, 1200, 'bronze', '2024-10-10', '2025-01-10', NOW()),
(5, 'Vikram Rao', 'O-', '+91-9876543214', '1985-07-18', 'male', '654 Hitech City', 'Hyderabad', 'Telangana', 'India', 17.3850, 78.4867, 1, 1, 10, 1000, 'bronze', '2024-11-30', '2025-02-28', NOW());

-- Insert hospital profiles
INSERT INTO hospital_profiles (user_id, hospital_name, registration_number, phone, address, city, state, country, latitude, longitude, license_document, is_verified, created_at) VALUES
(6, 'City General Hospital', 'REG-MUM-001', '+91-22-12345678', '1 Hospital Road, Marine Lines', 'Mumbai', 'Maharashtra', 'India', 19.0760, 72.8777, 'license1.pdf', 1, NOW()),
(7, 'Apollo Blood Centre', 'REG-DEL-002', '+91-11-87654321', '2 Medical Complex, Connaught Place', 'Delhi', 'NCR', 'India', 28.6139, 77.2090, 'license2.pdf', 1, NOW()),
(8, 'St. John\'s Medical', 'REG-BLR-003', '+91-80-55556666', '3 Healthcare Ave, Koramangala', 'Bangalore', 'Karnataka', 'India', 12.9716, 77.5946, 'license3.pdf', 1, NOW());

-- Insert blood requests
INSERT INTO blood_requests (hospital_id, patient_blood_type, units_needed, urgency, required_date, city, state, status, reason, contact_person, contact_phone, created_at) VALUES
(6, 'O-', 3, 'critical', NULL, 'Mumbai', 'Maharashtra', 'open', 'Emergency surgery - accident victim', 'Dr. Sharma', '+91-22-12345678', NOW()),
(7, 'A+', 2, 'urgent', '2025-02-15', 'Delhi', 'NCR', 'open', 'Scheduled cardiac surgery', 'Dr. Patel', '+91-11-87654321', NOW()),
(8, 'B+', 4, 'normal', '2025-02-20', 'Bangalore', 'Karnataka', 'open', 'Cancer patient transfusion', 'Dr. Kumar', '+91-80-55556666', NOW());

-- Update testimonials to link to actual donors
UPDATE testimonials SET donor_id = 1 WHERE id = 1;
UPDATE testimonials SET donor_id = 2 WHERE id = 2;

-- Insert achievements for donors
INSERT INTO achievements (donor_id, badge_type, badge_name, badge_description, awarded_at) VALUES
(1, 'lifesaver_gold', 'Gold Lifesaver', 'Completed 20+ blood donations', NOW()),
(1, 'regular_donor', 'Regular Donor', 'Donated 3+ times in a year', NOW()),
(2, 'lifesaver_silver', 'Silver Lifesaver', 'Completed 15+ blood donations', NOW()),
(3, 'lifesaver_silver', 'Silver Lifesaver', 'Completed 15+ blood donations', NOW()),
(4, 'lifesaver_bronze', 'Bronze Lifesaver', 'Completed 10+ blood donations', NOW()),
(5, 'lifesaver_bronze', 'Bronze Lifesaver', 'Completed 10+ blood donations', NOW()),
(5, 'universal_donor', 'Universal Donor', 'O- blood type - can donate to anyone', NOW());
