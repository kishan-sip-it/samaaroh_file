<?php
require_once 'config/config.php';

echo "<h2>Adding Catering Fields to Services Table</h2>";

try {
    // Check if is_fixed_thali column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM services LIKE 'is_fixed_thali'");
    $column = $stmt->fetch();
    
    if (!$column) {
        echo "<h3>Adding is_fixed_thali column...</h3>";
        $pdo->exec("ALTER TABLE services ADD COLUMN is_fixed_thali TINYINT(1) DEFAULT 0");
        echo "✅ is_fixed_thali column added<br>";
    } else {
        echo "✅ is_fixed_thali column already exists<br>";
    }
    
    // Check if thali_price_per_person column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM services LIKE 'thali_price_per_person'");
    $column = $stmt->fetch();
    
    if (!$column) {
        echo "<h3>Adding thali_price_per_person column...</h3>";
        $pdo->exec("ALTER TABLE services ADD COLUMN thali_price_per_person DECIMAL(10,2) DEFAULT 0.00");
        echo "✅ thali_price_per_person column added<br>";
    } else {
        echo "✅ thali_price_per_person column already exists<br>";
    }
    
    // Check if fixed_thali_menu column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM services LIKE 'fixed_thali_menu'");
    $column = $stmt->fetch();
    
    if (!$column) {
        echo "<h3>Adding fixed_thali_menu column...</h3>";
        $pdo->exec("ALTER TABLE services ADD COLUMN fixed_thali_menu TEXT DEFAULT NULL");
        echo "✅ fixed_thali_menu column added<br>";
    } else {
        echo "✅ fixed_thali_menu column already exists<br>";
    }
    
    // Show updated table structure
    echo "<h3>Updated Services Table:</h3>";
    $stmt = $pdo->query("DESCRIBE services");
    echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    while ($row = $stmt->fetch()) {
        if (strpos($row['Field'], 'thali') !== false || strpos($row['Field'], 'fixed') !== false) {
            echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
        }
    }
    echo "</table>";
    
    echo "<h3>✅ Database Setup Complete!</h3>";
    echo "<p>Services table now supports fixed thali catering with real-time pricing.</p>";
    echo "<p><a href='" . BASE_URL . "customer/dashboard.php'>Test the System</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
