<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login to delete services", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// VALIDATE POST REQUEST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['service_id'])) {
    setAlert("Invalid request", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

$service_id = intval($_POST['service_id']);

// FETCH SERVICE (VERIFY OWNERSHIP)
$stmt = $pdo->prepare("
    SELECT id, image_path, title 
    FROM services 
    WHERE id = ? AND provider_id = ?
");
$stmt->execute([$service_id, $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found or access denied", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

try {
    // DELETE IMAGE FILE IF EXISTS (WINDOWS-SAFE)
    if (!empty($service['image_path'])) {
        $image_path = UPLOADS_DIR . $service['image_path'];
        // Convert to Windows backslashes
        $image_path = str_replace('/', '\\', $image_path);
        
        if (file_exists($image_path)) {
            if (@unlink($image_path)) {
                error_log("Deleted image: " . $image_path);
            } else {
                error_log("Failed to delete image: " . $image_path);
            }
        }
    }
    
    // DELETE SERVICE FROM DATABASE
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND provider_id = ?");
    $stmt->execute([$service_id, $_SESSION['user_id']]);
    
    setAlert("Service '" . htmlspecialchars($service['title']) . "' deleted successfully!", "success");
} catch (PDOException $e) {
    error_log("Delete service error: " . $e->getMessage());
    setAlert("Failed to delete service. Please try again.", "error");
}

header("Location: " . BASE_URL . "provider/dashboard.php");
exit();
?>