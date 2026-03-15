<?php 
include 'config/config.php';
include 'includes/header.php';

// Catering menu items with pricing
$catering_menu = [
    'starters' => [
        ['id' => 1, 'name' => 'Kachori & Samosa', 'description' => 'Crispy fried snacks with chutneys', 'price_per_plate' => 300, 'image' => 'kachori.jpg'],
        ['id' => 2, 'name' => 'Paneer Tikka', 'description' => 'Grilled cottage cheese with spices', 'price_per_plate' => 350, 'image' => 'paneer_tikka.jpg'],
        ['id' => 3, 'name' => 'Dhokla', 'description' => 'Steamed gram flour cake', 'price_per_plate' => 250, 'image' => 'dhokla.jpg'],
        ['id' => 4, 'name' => 'Fafda & Jalebi', 'description' => 'Traditional Gujarati snack combo', 'price_per_plate' => 280, 'image' => 'fafda_jalebi.jpg']
    ],
    'main_course' => [
        ['id' => 5, 'name' => 'Dal Baati Churma', 'description' => 'Rajasthani delicacy with lentils', 'price_per_plate' => 500, 'image' => 'dal_baati.jpg'],
        ['id' => 6, 'name' => 'Undhiyu', 'description' => 'Mixed vegetable winter special', 'price_per_plate' => 450, 'image' => 'undhiyu.jpg'],
        ['id' => 7, 'name' => 'Gujarati Thali', 'description' => 'Complete traditional meal', 'price_per_plate' => 550, 'image' => 'gujarati_thali.jpg'],
        ['id' => 8, 'name' => 'Kadhi Khichdi', 'description' => 'Yogurt curry with rice', 'price_per_plate' => 400, 'image' => 'kadhi_khichdi.jpg']
    ],
    'desserts' => [
        ['id' => 9, 'name' => 'Basundi', 'description' => 'Thick sweetened milk dessert', 'price_per_plate' => 250, 'image' => 'basundi.jpg'],
        ['id' => 10, 'name' => 'Shrikhand', 'description' => 'Sweet yogurt with saffron', 'price_per_plate' => 200, 'image' => 'shrikhand.jpg'],
        ['id' => 11, 'name' => 'Malpua', 'description' => 'Sweet pancakes with syrup', 'price_per_plate' => 180, 'image' => 'malpua.jpg'],
        ['id' => 12, 'name' => 'Gulab Jamun', 'description' => 'Fried milk balls in syrup', 'price_per_plate' => 150, 'image' => 'gulab_jamun.jpg']
    ],
    'beverages' => [
        ['id' => 13, 'name' => 'Masala Chaas', 'description' => 'Spiced buttermilk', 'price_per_plate' => 50, 'image' => 'chaas.jpg'],
        ['id' => 14, 'name' => 'Aam Panna', 'description' => 'Raw mango drink', 'price_per_plate' => 80, 'image' => 'aam_panna.jpg'],
        ['id' => 15, 'name' => 'Fruit Punch', 'description' => 'Fresh fruit juice mix', 'price_per_plate' => 120, 'image' => 'fruit_punch.jpg'],
        ['id' => 16, 'name' => 'Special Tea', 'description' => 'Gujarati masala chai', 'price_per_plate' => 60, 'image' => 'tea.jpg']
    ]
];

// Calculate totals
function calculateCateringTotal($selected_items, $guest_count) {
    $total = 0;
    $item_details = [];
    
    foreach ($selected_items as $item_id => $quantity) {
        foreach ($GLOBALS['catering_menu'] as $category => $items) {
            foreach ($items as $item) {
                if ($item['id'] == $item_id) {
                    $item_total = $item['price_per_plate'] * $quantity * $guest_count;
                    $total += $item_total;
                    $item_details[] = [
                        'name' => $item['name'],
                        'price_per_plate' => $item['price_per_plate'],
                        'quantity' => $quantity,
                        'guest_count' => $guest_count,
                        'total' => $item_total
                    ];
                    break 3;
                }
            }
        }
    }
    
    return ['total' => $total, 'items' => $item_details];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Menu | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .menu-item { transition: all 0.3s ease; }
        .menu-item:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .checkbox-wrapper { position: relative; }
        .checkbox-wrapper input[type="checkbox"] { position: absolute; opacity: 0; }
        .checkbox-custom { 
            display: inline-block; 
            width: 20px; 
            height: 20px; 
            border: 2px solid #e53e3e; 
            border-radius: 4px; 
            margin-right: 8px;
            transition: all 0.3s ease;
        }
        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom { 
            background: #e53e3e; 
            border-color: #e53e3e;
        }
        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom::after {
            content: '✓'; 
            color: white; 
            font-size: 12px; 
            display: block; 
            text-align: center; 
            line-height: 16px;
        }
    </style>
</head>
<body class="bg-stone-50">
    <?php include 'includes/navbar.php'; ?>

    <!-- Header Section -->
    <section class="bg-gradient-to-r from-rose-600 to-amber-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4">Premium Wedding Catering</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Authentic Gujarati cuisine crafted for your special day. Select items and get instant pricing.
            </p>
        </div>
    </section>

    <!-- Guest Count & Calculator -->
    <section class="py-12 bg-white sticky top-0 z-40 shadow-lg border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 items-center">
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Number of Guests</label>
                    <input type="number" id="guestCount" value="100" min="10" max="1000" 
                           class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none text-lg font-semibold">
                </div>
                <div class="text-center">
                    <div class="text-sm text-stone-600 mb-1">Total Plates Required</div>
                    <div id="totalPlates" class="text-3xl font-bold text-rose-600">100</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-stone-600 mb-1">Total Cost</div>
                    <div id="totalCost" class="text-3xl font-bold text-rose-600">₹0</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Categories -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Starters -->
            <div class="mb-16">
                <h2 class="heading text-3xl font-bold mb-8 text-stone-800 flex items-center">
                    <span class="text-2xl mr-3">🥟</span> Starters
                </h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($catering_menu['starters'] as $item): ?>
                    <div class="menu-item bg-white rounded-2xl shadow-lg overflow-hidden border border-stone-200">
                        <div class="h-48 bg-cover bg-center" style="background-image: url('/samaaroh_file/images/catering/<?= $item['image'] ?>')"></div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-2 text-stone-800"><?= $item['name'] ?></h3>
                            <p class="text-stone-600 text-sm mb-4"><?= $item['description'] ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-rose-600">₹<?= number_format($item['price_per_plate']) ?></span>
                                <span class="text-sm text-stone-500">per plate</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="item_<?= $item['id'] ?>" 
                                           data-price="<?= $item['price_per_plate'] ?>" 
                                           data-name="<?= $item['name'] ?>"
                                           onchange="updateTotal()">
                                    <span class="checkbox-custom"></span>
                                </div>
                                <label for="item_<?= $item['id'] ?>" class="text-stone-700 font-medium cursor-pointer">Select</label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Main Course -->
            <div class="mb-16">
                <h2 class="heading text-3xl font-bold mb-8 text-stone-800 flex items-center">
                    <span class="text-2xl mr-3">🍛</span> Main Course
                </h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($catering_menu['main_course'] as $item): ?>
                    <div class="menu-item bg-white rounded-2xl shadow-lg overflow-hidden border border-stone-200">
                        <div class="h-48 bg-cover bg-center" style="background-image: url('/samaaroh_file/images/catering/<?= $item['image'] ?>')"></div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-2 text-stone-800"><?= $item['name'] ?></h3>
                            <p class="text-stone-600 text-sm mb-4"><?= $item['description'] ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-rose-600">₹<?= number_format($item['price_per_plate']) ?></span>
                                <span class="text-sm text-stone-500">per plate</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="item_<?= $item['id'] ?>" 
                                           data-price="<?= $item['price_per_plate'] ?>" 
                                           data-name="<?= $item['name'] ?>"
                                           onchange="updateTotal()">
                                    <span class="checkbox-custom"></span>
                                </div>
                                <label for="item_<?= $item['id'] ?>" class="text-stone-700 font-medium cursor-pointer">Select</label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Desserts -->
            <div class="mb-16">
                <h2 class="heading text-3xl font-bold mb-8 text-stone-800 flex items-center">
                    <span class="text-2xl mr-3">🍮</span> Desserts
                </h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($catering_menu['desserts'] as $item): ?>
                    <div class="menu-item bg-white rounded-2xl shadow-lg overflow-hidden border border-stone-200">
                        <div class="h-48 bg-cover bg-center" style="background-image: url('/samaaroh_file/images/catering/<?= $item['image'] ?>')"></div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-2 text-stone-800"><?= $item['name'] ?></h3>
                            <p class="text-stone-600 text-sm mb-4"><?= $item['description'] ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-rose-600">₹<?= number_format($item['price_per_plate']) ?></span>
                                <span class="text-sm text-stone-500">per plate</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="item_<?= $item['id'] ?>" 
                                           data-price="<?= $item['price_per_plate'] ?>" 
                                           data-name="<?= $item['name'] ?>"
                                           onchange="updateTotal()">
                                    <span class="checkbox-custom"></span>
                                </div>
                                <label for="item_<?= $item['id'] ?>" class="text-stone-700 font-medium cursor-pointer">Select</label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Beverages -->
            <div class="mb-16">
                <h2 class="heading text-3xl font-bold mb-8 text-stone-800 flex items-center">
                    <span class="text-2xl mr-3">🥤</span> Beverages
                </h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($catering_menu['beverages'] as $item): ?>
                    <div class="menu-item bg-white rounded-2xl shadow-lg overflow-hidden border border-stone-200">
                        <div class="h-48 bg-cover bg-center" style="background-image: url('/samaaroh_file/images/catering/<?= $item['image'] ?>')"></div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-2 text-stone-800"><?= $item['name'] ?></h3>
                            <p class="text-stone-600 text-sm mb-4"><?= $item['description'] ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-rose-600">₹<?= number_format($item['price_per_plate']) ?></span>
                                <span class="text-sm text-stone-500">per plate</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="item_<?= $item['id'] ?>" 
                                           data-price="<?= $item['price_per_plate'] ?>" 
                                           data-name="<?= $item['name'] ?>"
                                           onchange="updateTotal()">
                                    <span class="checkbox-custom"></span>
                                </div>
                                <label for="item_<?= $item['id'] ?>" class="text-stone-700 font-medium cursor-pointer">Select</label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Selected Items Summary -->
    <section id="selectedItems" class="py-12 bg-stone-100 hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Selected Items</h2>
            <div id="itemsList" class="space-y-3">
                <!-- Items will be populated by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Action Section -->
    <section class="py-16 bg-rose-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="heading text-3xl font-bold mb-6">Ready to Book?</h2>
            <p class="text-xl mb-8 opacity-90">Get instant quote and book your catering service</p>
            <button onclick="proceedToBooking()" 
                    class="bg-white text-rose-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-100 transition transform hover:scale-105 shadow-xl">
                Get Catering Quote
            </button>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
    let selectedItems = {};
    let guestCount = 100;

    function updateTotal() {
        // Get guest count
        guestCount = parseInt(document.getElementById('guestCount').value) || 100;
        document.getElementById('totalPlates').textContent = guestCount;
        
        // Clear selected items
        selectedItems = {};
        
        // Get all checked items
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        
        checkboxes.forEach(checkbox => {
            const price = parseInt(checkbox.dataset.price);
            const name = checkbox.dataset.name;
            selectedItems[checkbox.id] = {
                name: name,
                price: price,
                total: price * guestCount
            };
        });
        
        // Calculate total
        let totalCost = 0;
        for (let item in selectedItems) {
            totalCost += selectedItems[item].total;
        }
        
        // Update display
        document.getElementById('totalCost').textContent = '₹' + totalCost.toLocaleString('en-IN');
        
        // Update selected items section
        updateSelectedItems();
        
        // Show/hide selected items section
        const selectedSection = document.getElementById('selectedItems');
        if (Object.keys(selectedItems).length > 0) {
            selectedSection.classList.remove('hidden');
        } else {
            selectedSection.classList.add('hidden');
        }
    }

    function updateSelectedItems() {
        const itemsList = document.getElementById('itemsList');
        itemsList.innerHTML = '';
        
        for (let itemId in selectedItems) {
            const item = selectedItems[itemId];
            const itemDiv = document.createElement('div');
            itemDiv.className = 'bg-white p-4 rounded-xl flex justify-between items-center';
            itemDiv.innerHTML = `
                <div>
                    <h4 class="font-semibold text-stone-800">${item.name}</h4>
                    <p class="text-stone-600">₹${item.price} per plate × ${guestCount} guests</p>
                </div>
                <div class="text-right">
                    <div class="font-bold text-lg text-rose-600">₹${item.total.toLocaleString('en-IN')}</div>
                </div>
            `;
            itemsList.appendChild(itemDiv);
        }
    }

    function proceedToBooking() {
        if (Object.keys(selectedItems).length === 0) {
            alert('Please select at least one item from the menu.');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('guest_count', guestCount);
        formData.append('selected_items', JSON.stringify(selectedItems));
        
        // Calculate total
        let total = 0;
        for (let item in selectedItems) {
            total += selectedItems[item].total;
        }
        formData.append('total_cost', total);
        
        // Store in session (you'll need to implement server-side handling)
        fetch('<?= BASE_URL ?>customer/save_catering_selection.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>customer/catering_booking.php';
            } else {
                alert('Error saving selection. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving selection. Please try again.');
        });
    }

    // Update total when guest count changes
    document.getElementById('guestCount').addEventListener('input', updateTotal);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', updateTotal);
    </script>
</body>
</html>
