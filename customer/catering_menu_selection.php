<?php
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Get catering services
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email 
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE s.service_type = 'catering' AND s.status = 'available' 
    ORDER BY s.created_at DESC
");
$stmt->execute();
$catering_services = $stmt->fetchAll();

// Handle menu selection
$selected_service = null;
$menu_data = null;
$validation_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'select_service') {
        $service_id = $_POST['service_id'];
        
        $stmt = $pdo->prepare("
            SELECT s.*, u.name as provider_name 
            FROM services s 
            JOIN users u ON s.provider_id = u.id 
            WHERE s.id = ? AND s.service_type = 'catering' AND s.status = 'available'
        ");
        $stmt->execute([$service_id]);
        $selected_service = $stmt->fetch();
        
        if ($selected_service) {
            $menu_data = json_decode($selected_service['menu_data'], true);
            $_SESSION['selected_service_id'] = $service_id;
        }
    } elseif ($_POST['action'] === 'submit_order') {
        // Validate that at least one item is selected
        if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
            $validation_error = "Please select at least one menu item before submitting your order.";
        } else {
            // Store order in session
            $_SESSION['catering_order'] = [
                'service_id' => $_POST['service_id'],
                'selected_items' => $_POST['selected_items'],
                'guest_count' => $_POST['guest_count'] ?? 50,
                'total_price' => $_POST['total_price'],
                'special_requests' => $_POST['special_requests'] ?? ''
            ];
            
            header('Location: ' . BASE_URL . 'customer/catering_confirmation.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Menu Selection | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .menu-item { transition: all 0.3s ease; }
        .menu-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .checkbox-custom { 
            position: relative; 
            width: 20px; 
            height: 20px; 
            border: 2px solid #d1d5db; 
            border-radius: 4px; 
            transition: all 0.3s ease;
        }
        .checkbox-custom.checked { 
            background-color: #dc2626; 
            border-color: #dc2626;
        }
        .checkbox-custom.checked:after { 
            content: '✓'; 
            color: white; 
            position: absolute; 
            top: -2px; 
            left: 3px; 
            font-size: 12px; 
        }
    </style>
</head>
<body class="bg-stone-50">

    <!-- Header Section -->
    <section class="bg-gradient-to-r from-rose-600 to-amber-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4">Select Your Catering Menu</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Choose from our curated catering providers and customize your menu
            </p>
        </div>
    </section>

    <?php if ($validation_error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <strong>Validation Error:</strong> <?= $validation_error ?>
        </div>
    <?php endif; ?>

    <?php if (!$selected_service): ?>
        <!-- Service Selection -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="heading text-3xl font-bold mb-8 text-stone-800 text-center">Choose Catering Provider</h2>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($catering_services as $service): ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-stone-200 hover:shadow-2xl transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-xl text-stone-800"><?= htmlspecialchars($service['service_name']) ?></h3>
                                    <p class="text-stone-600 text-sm">by <?= htmlspecialchars($service['provider_name']) ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-rose-600">₹<?= number_format($service['price']) ?></div>
                                    <div class="text-sm text-stone-500">per plate</div>
                                </div>
                            </div>
                            
                            <p class="text-stone-600 mb-4"><?= htmlspecialchars($service['description']) ?></p>
                            
                            <form method="POST" class="mt-4">
                                <input type="hidden" name="action" value="select_service">
                                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                <button type="submit" 
                                        class="w-full bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                                    View Menu & Select Items
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($catering_services)): ?>
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">🍽</div>
                    <h3 class="heading text-2xl font-bold text-stone-800 mb-4">No Catering Services Available</h3>
                    <p class="text-stone-600 mb-8">Check back soon for new catering providers in your area.</p>
                    <a href="<?= BASE_URL ?>register.php?role=provider" 
                       class="bg-rose-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                        Become a Catering Provider
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Menu Selection -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Provider Info -->
                <div class="bg-gradient-to-r from-rose-50 to-amber-50 rounded-2xl p-6 mb-8">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="font-bold text-lg text-stone-800 mb-2">Provider</h3>
                            <p class="text-stone-600"><?= htmlspecialchars($selected_service['provider_name']) ?></p>
                            <p class="text-sm text-stone-500">Verified Catering Service</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-stone-800 mb-2">Base Price</h3>
                            <p class="text-2xl font-bold text-rose-600">₹<?= number_format($selected_service['price']) ?></p>
                            <p class="text-sm text-stone-500">per plate</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-stone-800 mb-2">Service Type</h3>
                            <p class="text-xl font-semibold text-stone-800">Traditional Catering</p>
                            <p class="text-sm text-stone-500">Authentic Gujarati Menu</p>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <form method="POST" id="menu_form">
                    <input type="hidden" name="action" value="submit_order">
                    <input type="hidden" name="service_id" value="<?= $selected_service['id'] ?>">
                    <input type="hidden" name="total_price" id="total_price" value="0">
                    
                    <?php if (isset($menu_data['categories'])): ?>
                        <!-- Built-in Thali Structure -->
                        <?php foreach ($menu_data['categories'] as $category => $items): ?>
                        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                            <h2 class="heading text-2xl font-bold mb-6 text-stone-800">
                                <?= ucfirst(str_replace('_', ' ', $category)) ?>
                            </h2>
                            
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($items as $index => $item): ?>
                                <div class="menu-item bg-stone-50 rounded-xl p-6 border border-stone-200">
                                    <div class="flex items-start space-x-3">
                                        <input type="checkbox" 
                                               name="selected_items[<?= $category ?>][<?= $index ?>]" 
                                               value="<?= json_encode($item) ?>"
                                               onchange="updateTotal()"
                                               class="mt-1">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-lg text-stone-800 mb-2">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </h4>
                                            <p class="text-stone-600 text-sm mb-3">
                                                <?= htmlspecialchars($item['description']) ?>
                                            </p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-2xl font-bold text-rose-600">
                                                    ₹<?= number_format($item['price']) ?>
                                                </span>
                                                <span class="text-sm text-stone-500">per plate</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Checklist Structure -->
                        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                            <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Menu Items</h2>
                            
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($menu_data as $index => $item): ?>
                                <div class="menu-item bg-stone-50 rounded-xl p-6 border border-stone-200">
                                    <div class="flex items-start space-x-3">
                                        <input type="checkbox" 
                                               name="selected_items[<?= $index ?>]" 
                                               value="<?= json_encode($item) ?>"
                                               onchange="updateTotal()"
                                               class="mt-1">
                                        <div class="flex-1">
                                            <h4 class="font-bold text-lg text-stone-800 mb-2">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </h4>
                                            <p class="text-stone-600 text-sm mb-3">
                                                <?= htmlspecialchars($item['description']) ?>
                                            </p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-2xl font-bold text-rose-600">
                                                    ₹<?= number_format($item['price']) ?>
                                                </span>
                                                <span class="text-sm text-stone-500">per plate</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Order Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                        <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Order Details</h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Number of Guests *</label>
                                <input type="number" name="guest_count" id="guest_count" 
                                       value="50" min="10" max="1000" required
                                       onchange="updateTotal()"
                                       class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 mb-2">Total Price</label>
                                <div class="text-3xl font-bold text-rose-600" id="total_display">₹0</div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Special Requests</label>
                            <textarea name="special_requests" rows="3"
                                      class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                      placeholder="Any dietary restrictions, preferences, or special requirements..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" 
                            class="bg-rose-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-rose-700 transition transform hover:scale-105 shadow-xl">
                        Submit Catering Order
                    </button>
                </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <?php include '../includes/footer.php'; ?>

    <script>
    function updateTotal() {
        const guestCount = parseInt(document.getElementById('guest_count').value) || 50;
        let totalPrice = 0;
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                try {
                    const item = JSON.parse(checkbox.value);
                    totalPrice += item.price * guestCount;
                } catch (e) {
                    console.error('Error parsing item data:', e);
                }
            }
        });
        
        document.getElementById('total_display').textContent = '₹' + totalPrice.toLocaleString('en-IN');
        document.getElementById('total_price').value = totalPrice;
    }

    // Initialize total on page load
    document.addEventListener('DOMContentLoaded', updateTotal);
    </script>
</body>
</html>
    <?php include '../includes/footer.php'; ?>
