<?php
require_once 'config/config.php';

echo "<h2>BOOKINGS TABLE STRUCTURE:</h2><pre>";
try {
    $stmt = $pdo->query('DESCRIBE bookings');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>SERVICES TABLE STRUCTURE:</h2><pre>";
try {
    $stmt = $pdo->query('DESCRIBE services');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>PACKAGES TABLE STRUCTURE:</h2><pre>";
try {
    $stmt = $pdo->query('DESCRIBE packages');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
