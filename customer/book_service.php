<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to book services", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// VALIDATE POST REQUEST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['service_id']) || empty($_POST['price']) || empty($_POST['event_date'])) {
    setAlert("Invalid booking request. Please select service and date.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

$service_id = intval($_POST['service_id']);
$price = floatval($_POST['price']);
$event_date = trim($_POST['event_date']);

// VALIDATE WEDDING DATE (MUST BE AT LEAST 7 DAYS IN FUTURE)
$min_date = date('Y-m-d', strtotime('+7 days'));
if (strtotime($event_date) < strtotime($min_date)) {
    setAlert("Wedding date must be at least 7 days from today. Select a future date.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php#services");
    exit();
}

// FETCH SERVICE DETAILS (VERIFY EXISTS + AVAILABLE)
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

// VALIDATE PRICE MATCH (PREVENT TAMPERING)
if (abs($price - $service['price']) > 0.01) {
    setAlert("Price mismatch. Service price has changed. Please refresh and try again.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// CREATE BOOKING (STATUS = PENDING - 12 HOUR ACCEPTANCE WINDOW)
try {
    $stmt = $pdo->prepare("
        INSERT INTO bookings (customer_id, service_id, total_price, event_date, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $service_id,
        $price,
        $event_date
    ]);
    
    setAlert("✅ Booking request sent to " . htmlspecialchars($service['provider_name']) . "! They have 12 hours to accept.", "success");
} catch (PDOException $e) {
    error_log("Booking creation error: " . $e->getMessage());
    setAlert("Failed to create booking. Please try again later.", "error");
}

header("Location: " . BASE_URL . "customer/dashboard.php");
exit();
?>