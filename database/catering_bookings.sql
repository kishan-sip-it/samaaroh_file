-- Create catering_bookings table for storing catering orders
CREATE TABLE IF NOT EXISTS catering_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue_location TEXT NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    special_requirements TEXT,
    guest_count INT NOT NULL,
    selected_items JSON NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_catering_customer_id ON catering_bookings(customer_id);
CREATE INDEX idx_catering_status ON catering_bookings(status);
CREATE INDEX idx_catering_event_date ON catering_bookings(event_date);
