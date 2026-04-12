<?php
require_once '../config/config.php';

// Auth Check: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to book services", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Validate POST Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['service_id']) || empty($_POST['price'])) {
    setAlert("Invalid booking request. Please select service.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

$service_id = intval($_POST['service_id']);
$price = floatval($_POST['price']);
$event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : null;
$guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 50;

// Calculate total price
$total_price = $price;

// Fetch Service Details (verify exists + available)
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name 
    FROM services s
    JOIN users u ON s.provider_id = u.id
    WHERE s.id = ? AND s.is_available = 1
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found or unavailable for booking.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Validate Price Match (prevent tampering)
if (abs($price - $service['price']) > 0.01) {
    setAlert("Price mismatch. Service price has changed. Please refresh and try again.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Validate guest count based on service category
$service_category = $service['category'] ?? '';
if ($service_category !== 'catering') {
    $guest_count = 1; // Default to 1 for non-catering services
}

// Create Booking (status = pending - 12 hour acceptance window)
try {
    // First check if bookings table exists and create if needed
    $check_table = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($check_table->rowCount() == 0) {
        // Create bookings table if it doesn't exist
        $create_table_sql = "
            CREATE TABLE bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                service_id INT NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                event_date DATE,
                guest_count INT DEFAULT 1,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_customer (customer_id),
                INDEX idx_service (service_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $pdo->exec($create_table_sql);
        
        // Log table creation
        error_log("Bookings table created successfully");
    }
    
    // Insert booking with better error handling
    $stmt = $pdo->prepare("
        INSERT INTO bookings (customer_id, service_id, total_price, event_date, guest_count, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    $result = $stmt->execute([
        $_SESSION['user_id'],
        $service_id,
        $total_price,
        $event_date,
        $guest_count
    ]);
    
    if ($result) {
        $booking_id = $pdo->lastInsertId();
        error_log("Booking created successfully: ID $booking_id for customer {$_SESSION['user_id']}, service $service_id");
        setAlert("✅ Booking request sent to " . htmlspecialchars($service['provider_name']) . "! They have 12 hours to accept.", "success");
    } else {
        throw new Exception("Failed to insert booking record");
    }
    
} catch (PDOException $e) {
    error_log("Booking creation PDO error: " . $e->getMessage());
    error_log("SQL State: " . $e->errorInfo[0]);
    error_log("Error Code: " . $e->errorInfo[1]);
    error_log("Error Details: " . $e->errorInfo[2]);
    setAlert("Failed to create booking. Please try again later. (Error: " . $e->errorInfo[1] . ")", "error");
} catch (Exception $e) {
    error_log("Booking creation general error: " . $e->getMessage());
    setAlert("Failed to create booking. Please try again later.", "error");
}

header("Location: " . BASE_URL . "customer/dashboard.php");
exit();
?>
