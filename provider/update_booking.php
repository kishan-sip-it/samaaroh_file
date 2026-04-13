<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login to manage bookings", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// VALIDATE POST REQUEST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['booking_id']) || empty($_POST['action'])) {
    setAlert("Invalid request", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

$booking_id = intval($_POST['booking_id']);
$action = $_POST['action']; // 'accept' or 'reject'

// FETCH BOOKING (VERIFY IT BELONGS TO THIS PROVIDER)
$stmt = $pdo->prepare("
    SELECT b.*, s.provider_id, s.title as service_title 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    setAlert("Booking not found", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// VERIFY PROVIDER OWNERSHIP
if ($booking['provider_id'] != $_SESSION['user_id']) {
    setAlert("Access denied. This booking doesn't belong to you.", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// CHECK 12-HOUR WINDOW (CRITICAL FOR WEDDING WORKFLOW)
$booking_time = strtotime($booking['created_at']); // Use created_at instead of booking_date
$time_elapsed = time() - $booking_time;
$twelve_hours = 12 * 60 * 60; // 43200 seconds

if ($time_elapsed > $twelve_hours) {
    setAlert("❌ Cannot " . $action . " this booking. 12-hour window has expired.", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// PROCESS ACTION (UPDATED FOR ADVANCE PAYMENT FLOW)
try {
    $new_status = ($action === 'accept') ? 'confirmed' : 'cancelled'; // Use correct status values
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $booking_id]);
    
    if ($action === 'accept') {
        setAlert("✅ Booking accepted! Customer will be notified to make payment.", "success");
    } else {
        setAlert("❌ Booking rejected. Customer notified.", "info");
    }
} catch (PDOException $e) {
    error_log("Booking update error: " . $e->getMessage());
    setAlert("Failed to update booking. Please try again.", "error");
}

header("Location: " . BASE_URL . "provider/dashboard.php");
exit();
?>