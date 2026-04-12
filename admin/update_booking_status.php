<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$booking_id = $_GET['id'] ?? null;
$new_status = $_GET['status'] ?? null;

if (!$booking_id || !in_array($new_status, ['pending', 'confirmed', 'paid', 'completed', 'cancelled'])) {
    setAlert("Invalid request", "error");
    header("Location: " . BASE_URL . "admin/manage_bookings.php");
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $result = $stmt->execute([$new_status, $booking_id]);
    
    if ($result) {
        setAlert("Booking status updated to " . ucfirst($new_status), "success");
    } else {
        setAlert("Failed to update booking status", "error");
    }
} catch (PDOException $e) {
    error_log("Update booking status error: " . $e->getMessage());
    setAlert("Database error occurred", "error");
}

header("Location: " . BASE_URL . "admin/manage_bookings.php");
exit();
?>
