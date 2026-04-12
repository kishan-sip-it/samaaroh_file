<?php
require_once 'config/config.php';

echo "<h2>Fix Database Column Length</h2>";

try {
    // Check current status column
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    $column = $stmt->fetch();
    
    echo "<h3>Current Status Column:</h3>";
    echo "Field: " . $column['Field'] . "<br>";
    echo "Type: " . $column['Type'] . "<br>";
    echo "Null: " . $column['Null'] . "<br>";
    echo "Key: " . $column['Key'] . "<br>";
    echo "Default: " . $column['Default'] . "<br>";
    
    // Check if advance_paid column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'advance_paid'");
    $advance_column = $stmt->fetch();
    
    if ($advance_column) {
        echo "<h3>Advance Paid Column:</h3>";
        echo "Field: " . $advance_column['Field'] . "<br>";
        echo "Type: " . $advance_column['Type'] . "<br>";
    } else {
        echo "<h3>Advance Paid Column: NOT FOUND</h3>";
    }
    
    // Fix the status column to be VARCHAR(20)
    echo "<h3>Fixing Status Column...</h3>";
    
    $current_type = $column['Type'];
    if (strpos($current_type, 'enum') !== false) {
        echo "Converting ENUM to VARCHAR(20)...<br>";
        $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    } elseif (strpos($current_type, 'varchar') !== false) {
        preg_match('/varchar\((\d+)\)/', $current_type, $matches);
        $current_length = isset($matches[1]) ? (int)$matches[1] : 10;
        echo "Current VARCHAR length: $current_length<br>";
        
        if ($current_length < 20) {
            echo "Extending to VARCHAR(20)...<br>";
            $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        } else {
            echo "VARCHAR length is already sufficient<br>";
        }
    }
    
    // Create advance_paid column if it doesn't exist
    if (!$advance_column) {
        echo "<h3>Creating Advance Paid Column...</h3>";
        $pdo->exec("ALTER TABLE bookings ADD COLUMN advance_paid TINYINT(1) DEFAULT 0");
        echo "✅ advance_paid column created<br>";
    }
    
    // Verify the changes
    echo "<h3>Updated Structure:</h3>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'status'");
    $updated_status = $stmt->fetch();
    echo "Status Column: " . $updated_status['Type'] . "<br>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'advance_paid'");
    $updated_advance = $stmt->fetch();
    if ($updated_advance) {
        echo "Advance Paid Column: " . $updated_advance['Type'] . "<br>";
    }
    
    // Test status update
    echo "<h3>Testing Status Updates...</h3>";
    
    $test_values = ['pending', 'confirmed', 'advance_paid', 'paid', 'completed', 'cancelled'];
    
    foreach ($test_values as $status) {
        try {
            // Find a booking to test with
            $stmt = $pdo->query("SELECT id FROM bookings LIMIT 1");
            $test_booking = $stmt->fetch();
            
            if ($test_booking) {
                $test_id = $test_booking['id'];
                
                // Test updating status
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $result = $stmt->execute([$status, $test_id]);
                
                if ($result) {
                    echo "✅ '$status' - SUCCESS<br>";
                } else {
                    echo "❌ '$status' - FAILED<br>";
                }
            }
        } catch (Exception $e) {
            echo "❌ '$status' - ERROR: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>✅ Database Fix Complete!</h3>";
    echo "<p>The database columns are now properly configured for the payment system.</p>";
    echo "<p><a href='" . BASE_URL . "customer/my_bookings.php'>Test Payment Flow</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
