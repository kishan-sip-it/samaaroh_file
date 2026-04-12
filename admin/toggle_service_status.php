<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$service_id = $_GET['id'] ?? null;
$new_status = $_GET['status'] ?? null;

if (!$service_id || ($new_status !== '0' && $new_status !== '1')) {
    setAlert("Invalid request", "error");
    header("Location: " . BASE_URL . "admin/manage_services.php");
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE services SET is_available = ? WHERE id = ?");
    $result = $stmt->execute([$new_status, $service_id]);
    
    if ($result) {
        setAlert("Service status updated successfully", "success");
    } else {
        setAlert("Failed to update service status", "error");
    }
} catch (PDOException $e) {
    error_log("Toggle service status error: " . $e->getMessage());
    setAlert("Database error occurred", "error");
}

header("Location: " . BASE_URL . "admin/manage_services.php");
exit();
?>
