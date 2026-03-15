<?php
include 'config/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get POST data
$json_data = file_get_contents('php://input');
$booking_data = json_decode($json_data, true);

if (!$booking_data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid booking data']);
    exit();
}

try {
    // Insert catering booking into database
    $stmt = $pdo->prepare("INSERT INTO catering_bookings (
        customer_id, 
        event_date, 
        event_time, 
        venue_location, 
        contact_person, 
        mobile_number, 
        special_requirements, 
        guest_count, 
        selected_items, 
        total_cost, 
        status, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $booking_data['event_date'],
        $booking_data['event_time'],
        $booking_data['venue_location'],
        $booking_data['contact_person'],
        $booking_data['mobile_number'],
        $booking_data['special_requirements'],
        $booking_data['guest_count'],
        json_encode($booking_data['selected_items']),
        $booking_data['total_cost']
    ]);

    // Clear catering selection from session
    unset($_SESSION['catering_selection']);

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Catering booking confirmed successfully']);

} catch (PDOException $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
