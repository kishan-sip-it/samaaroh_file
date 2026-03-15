<?php
include 'config/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get POST data
$guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 100;
$selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];
$total_cost = isset($_POST['total_cost']) ? intval($_POST['total_cost']) : 0;

// Store in session
$_SESSION['catering_selection'] = [
    'guest_count' => $guest_count,
    'selected_items' => $selected_items,
    'total_cost' => $total_cost,
    'created_at' => date('Y-m-d H:i:s')
];

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Catering selection saved successfully']);
?>
