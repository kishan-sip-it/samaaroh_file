<?php
require_once '../config/config.php';

// AUTH CHECK: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Admin access required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE REPORT RESOLUTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW(), resolved_by = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $report_id]);
        setAlert("Report marked as resolved", "success");
    } catch (PDOException $e) {
        error_log("Error resolving report: " . $e->getMessage());
        setAlert("Failed to resolve report", "error");
    }
}

header("Location: " . BASE_URL . "admin/view_reports.php");
exit();
?>
