<?php
require_once 'config/config.php';

echo "<h2>Fixing Booking Status Column</h2>";

try {
    // Check current table structure
    echo "<h3>Current Bookings Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE bookings");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
    
    // Check if status column needs to be modified
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    $status_column = $stmt->fetch();
    
    echo "<h3>Status Column Details:</h3>";
    echo "Type: " . $status_column['Type'] . "<br>";
    
    // Modify status column to accommodate longer values
    if (strpos($status_column['Type'], 'varchar') !== false) {
        // Extract current length
        preg_match('/varchar\((\d+)\)/', $status_column['Type'], $matches);
        $current_length = isset($matches[1]) ? (int)$matches[1] : 20;
        
        echo "Current VARCHAR length: $current_length<br>";
        
        if ($current_length < 15) {
            echo "Need to extend status column to VARCHAR(15)<br>";
            
            // Modify the column
            $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status VARCHAR(15) NOT NULL DEFAULT 'pending'");
            echo "✅ Status column updated to VARCHAR(15)<br>";
        } else {
            echo "✅ Status column length is sufficient<br>";
        }
    }
    
    // Update ENUM if it's an ENUM type
    if (strpos($status_column['Type'], 'enum') !== false) {
        echo "Status column is ENUM type - need to add new values<br>";
        
        // Modify ENUM to include new statuses
        $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'confirmed', 'advance_paid', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        echo "✅ ENUM updated with new status values<br>";
    }
    
    // Verify the change
    echo "<h3>Updated Status Column:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    $updated_column = $stmt->fetch();
    echo "New Type: " . $updated_column['Type'] . "<br>";
    
    // Test with a sample update
    echo "<h3>Testing Status Update:</h3>";
    $test_id = 1; // Use first booking for testing
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'advance_paid' WHERE id = ? AND status = 'confirmed'");
    $result = $stmt->execute([$test_id]);
    
    if ($result) {
        echo "✅ Status update test successful<br>";
        
        // Revert for safety
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND status = 'advance_paid'");
        $stmt->execute([$test_id]);
        echo "✅ Test reverted successfully<br>";
    } else {
        echo "❌ Status update test failed<br>";
    }
    
    echo "<h3>✅ Booking status column fix completed!</h3>";
    echo "<p><a href='" . BASE_URL . "customer/my_bookings.php'>Test Payment Flow</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
