<?php
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Get order details from session
$order = $_SESSION['catering_order'] ?? null;

if (!$order) {
    header('Location: ' . BASE_URL . 'customer/catering_menu_selection.php');
    exit();
}

// Get service details
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email 
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$order['service_id']]);
$service = $stmt->fetch();

// Process selected items - decode JSON if needed
$selected_items = [];
if (isset($order['selected_items'])) {
    // Handle both nested and flat structures
    if (is_array($order['selected_items'])) {
        foreach ($order['selected_items'] as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_string($item)) {
                        // Decode JSON string
                        $decoded_item = json_decode($item, true);
                        if ($decoded_item) {
                            $selected_items[] = $decoded_item;
                        }
                    } elseif (is_array($item)) {
                        // Already an array (properly formatted)
                        $selected_items[] = $item;
                    }
                }
            } elseif (is_string($items)) {
                // Single item as JSON string
                $decoded_item = json_decode($items, true);
                if ($decoded_item) {
                    $selected_items[] = $decoded_item;
                }
            }
        }
    }
}

// Process order submission
$order_placed = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $stmt = $pdo->prepare("INSERT INTO catering_bookings (
            service_id, 
            customer_id, 
            provider_id, 
            event_date, 
            event_time, 
            venue_location, 
            contact_person, 
            mobile_number, 
            special_requirements, 
            guest_count, 
            selected_menu, 
            total_cost, 
            status, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $order['service_id'],
            $_SESSION['user_id'],
            $service['provider_id'],
            $_POST['event_date'],
            $_POST['event_time'] ?? '18:00',
            $_POST['venue_location'],
            $_POST['contact_person'],
            $_POST['mobile_number'],
            $_POST['special_requirements'] ?? '',
            $order['guest_count'],
            json_encode($order['selected_items']),
            $order['total_price'],
            'pending'
        ]);
        
        $order_placed = true;
        
        // Clear session
        unset($_SESSION['catering_order']);
        unset($_SESSION['selected_service_id']);
        
    } catch (PDOException $e) {
        $error_message = "Error placing order: " . $e->getMessage();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Order Confirmation | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50">

    <!-- Header Section -->
    <section class="bg-gradient-to-r from-rose-600 to-amber-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4">Catering Order Confirmation</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Review your order details and confirm your booking
            </p>
        </div>
    </section>

    <?php if ($order_placed): ?>
        <!-- Success Message -->
        <section class="py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-8 rounded-2xl text-center">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="heading text-3xl font-bold mb-4 text-green-800">Order Placed Successfully!</h2>
                    <p class="text-xl text-green-700 mb-6">
                        Your catering order has been sent to the provider. They will contact you within 12 hours.
                    </p>
                    <div class="bg-white rounded-xl p-6 mb-6">
                        <h3 class="font-bold text-lg mb-3 text-stone-800">Order Reference</h3>
                        <p class="text-2xl font-bold text-rose-600">#<?= str_pad($pdo->lastInsertId(), 6, '0', STR_PAD_LEFT) ?></p>
                    </div>
                    <a href="<?= BASE_URL ?>customer/dashboard.php" 
                       class="bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <!-- Order Review Form -->
        <section class="py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-8">
                        <strong>Error:</strong> <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                    <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Order Summary</h2>
                    
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="font-bold text-lg mb-4 text-stone-700">Provider Details</h3>
                            <div class="space-y-2">
                                <p><strong>Name:</strong> <?= htmlspecialchars($service['provider_name']) ?></p>
                                <p><strong>Service:</strong> <?= htmlspecialchars($service['title'] ?? 'Catering Service') ?></p>
                                <p><strong>Base Price:</strong> ₹<?= number_format($service['price']) ?>/plate</p>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-4 text-stone-700">Order Summary</h3>
                            <div class="space-y-2">
                                <p><strong>Guests:</strong> <?= $order['guest_count'] ?></p>
                                <p><strong>Items Selected:</strong> <?= count($selected_items) ?> menu items</p>
                                <p><strong>Total Cost:</strong> <span class="text-2xl font-bold text-rose-600">₹<?= number_format($order['total_price']) ?></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selected Items -->
                    <h3 class="font-bold text-lg mb-4 text-stone-700">Selected Menu Items</h3>
                    <div class="bg-stone-50 rounded-xl p-6">
                        <?php if (!empty($selected_items)): ?>
                            <?php foreach ($selected_items as $item): ?>
                            <div class="flex justify-between items-center py-2 border-b border-stone-200">
                                <div>
                                    <p class="font-semibold text-stone-800"><?= htmlspecialchars($item['name'] ?? 'Menu Item') ?></p>
                                    <p class="text-sm text-stone-600"><?= htmlspecialchars($item['description'] ?? 'Delicious dish') ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-rose-600">₹<?= number_format($item['price'] ?? 0) ?></p>
                                    <p class="text-sm text-stone-500">per plate</p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <p class="text-stone-500">No menu items selected</p>
                                <p class="text-sm text-stone-400 mt-2">Please go back and select menu items</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($order['special_requests'])): ?>
                    <div class="mt-4">
                        <h3 class="font-bold text-lg mb-2 text-stone-700">Special Requests</h3>
                        <p class="bg-amber-50 p-4 rounded-lg"><?= htmlspecialchars($order['special_requests']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Event Details Form -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Event Details</h2>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Event Date *</label>
                                <input type="date" name="event_date" required
                                       class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Event Time *</label>
                                <input type="time" name="event_time" required
                                       class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Venue Location *</label>
                                <input type="text" name="venue_location" required
                                       class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                       placeholder="Full venue address">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Contact Person *</label>
                                <input type="text" name="contact_person" required
                                       class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                       placeholder="Your name">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Mobile Number *</label>
                            <input type="tel" name="mobile_number" required
                                   class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                   placeholder="+91 98765 43210">
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" 
                                    class="bg-rose-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-rose-700 transition transform hover:scale-105 shadow-xl">
                                Confirm Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
