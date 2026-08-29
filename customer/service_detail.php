<?php
require_once '../config/config.php';

// Ensure UPLOADS_URL is defined
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . 'uploads/');
}

// Create gallery directory if it doesn't exist
$gallery_dir = UPLOADS_DIR . 'gallery/';
if (!is_dir($gallery_dir)) {
    mkdir($gallery_dir, 0755, true);
}

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Login required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($service_id <= 0) {
    setAlert("Invalid service", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Fetch service details with provider info
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email
    FROM services s
    JOIN users u ON s.provider_id = u.id
    WHERE s.id = ? AND s.is_available = 1 AND u.is_verified = 1
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Create service_gallery table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS service_gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_id INT NOT NULL,
            image_name VARCHAR(255) NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            caption TEXT NULL,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_service (service_id),
            INDEX idx_display_order (display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table creation failed, continue without gallery
    $gallery_images = [];
}

// Fetch gallery images
try {
    $gallery_stmt = $pdo->prepare("
        SELECT * FROM service_gallery 
        WHERE service_id = ? 
        ORDER BY display_order ASC, created_at DESC
    ");
    $gallery_stmt->execute([$service_id]);
    $gallery_images = $gallery_stmt->fetchAll();
} catch (PDOException $e) {
    // Table doesn't exist or query failed
    $gallery_images = [];
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : null;
    $guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 1;
    
    // Validate
    if (empty($event_date)) {
        setAlert("Please select event date", "error");
    } elseif (strtotime($event_date) < strtotime('today')) {
        setAlert("Event date cannot be in the past", "error");
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO bookings (customer_id, service_id, total_price, event_date, guest_count, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $service_id,
                $service['price'],
                $event_date,
                $guest_count
            ]);
            
            setAlert("✅ Booking request sent to " . htmlspecialchars($service['provider_name']) . "! They have 12 hours to accept.", "success");
            header("Location: " . BASE_URL . "customer/my_bookings.php");
            exit();
            
        } catch (PDOException $e) {
            error_log("Booking error: " . $e->getMessage());
            setAlert("Failed to create booking. Please try again.", "error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .gallery-item { aspect-ratio: 1; overflow: hidden; }
        .lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; }
        .lightbox img { max-width: 90%; max-height: 90%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-40 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">✨</span>
                    <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700">SAMAAROH</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>customer/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">← Back to Dashboard</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php displayAlert(); ?>

        <!-- Service Header -->
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden mb-8">
            <!-- Main Image -->
            <div class="relative h-96 bg-stone-100">
                <?php if (!empty($service['image_path'])): ?>
                    <img src="<?= UPLOADS_URL . htmlspecialchars($service['image_path']) ?>" 
                         alt="<?= htmlspecialchars($service['title']) ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-100 to-stone-100">
                        <span class="text-6xl">
                            <?php
                            $icons = [
                                'bagiwala' => '🛺',
                                'party_plot' => '🎪',
                                'catering' => '🍲',
                                'photography' => '📸',
                                'decor' => '🎨',
                                'entertainment' => '🎵'
                            ];
                            echo $icons[$service['category']] ?? '🎪';
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <!-- Category Badge -->
                <div class="absolute top-4 left-4">
                    <span class="bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium text-stone-700">
                        <?= ucfirst(htmlspecialchars($service['category'])) ?>
                    </span>
                </div>
            </div>

            <!-- Service Info -->
            <div class="p-8">
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="md:col-span-2">
                        <h1 class="heading text-3xl font-bold text-stone-800 mb-4"><?= htmlspecialchars($service['title']) ?></h1>
                        
                        <div class="prose prose-stone max-w-none mb-6">
                            <p class="text-stone-600 leading-relaxed"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                        </div>

                        <!-- Provider Info -->
                        <div class="bg-stone-50 rounded-xl p-6 mb-6">
                            <h3 class="font-bold text-lg text-stone-800 mb-3">Service Provider</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-stone-900"><?= htmlspecialchars($service['provider_name']) ?></p>
                                    <p class="text-stone-600 text-sm"><?= htmlspecialchars($service['provider_phone']) ?></p>
                                    <p class="text-stone-600 text-sm"><?= htmlspecialchars($service['provider_email']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-amber-500 font-medium">⭐ 4.8 Rating</p>
                                    <p class="text-stone-500 text-sm">23 Reviews</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fixed Thali Menu Section -->
                    <?php if ($service['category'] === 'catering' && !empty($service['is_fixed_thali']) && $service['is_fixed_thali'] == 1): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">🍽️</span>
                            <h3 class="font-bold text-amber-800">Fixed Thali Menu</h3>
                        </div>
                        <p class="text-amber-700 text-sm mb-3">This caterer only provides fixed dishes</p>
                        
                        <?php if (!empty($service['fixed_thali_menu'])): ?>
                            <div class="bg-white rounded-lg p-3">
                                <h4 class="font-semibold text-amber-800 mb-2">Menu Items:</h4>
                                <div class="text-sm text-stone-700 space-y-1">
                                    <?php 
                                    $menu_items = explode("\n", $service['fixed_thali_menu']);
                                    foreach ($menu_items as $item): 
                                        if (!empty(trim($item))):
                                    ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-amber-500">•</span>
                                            <span><?= htmlspecialchars(trim($item)) ?></span>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3 bg-amber-100 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-amber-800">Price per Person:</span>
                                <span class="font-bold text-amber-900">₹<?= number_format($service['thali_price_per_person'], 0) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Booking Card -->
                    <div class="md:col-span-1">
                        <div class="bg-rose-50 rounded-xl p-6 sticky top-24">
                            <div class="text-center mb-6">
                                <?php if ($service['category'] === 'catering' && !empty($service['is_fixed_thali']) && $service['is_fixed_thali'] == 1): ?>
                                    <div class="space-y-2">
                                        <p class="text-2xl font-bold text-rose-600">₹<span id="perPersonPrice"><?= number_format($service['thali_price_per_person'], 0) ?></span></p>
                                        <p class="text-stone-600">per person</p>
                                        <div class="border-t pt-2 mt-2">
                                            <p class="text-3xl font-bold text-rose-700">₹<span id="totalPrice"><?= number_format($service['thali_price_per_person'] * 50, 0) ?></span></p>
                                            <p class="text-stone-600">total cost</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-3xl font-bold text-rose-600">₹<?= number_format($service['price'], 0) ?></p>
                                    <p class="text-stone-600">per service</p>
                                <?php endif; ?>
                            </div>

                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="price" id="calculatedPrice" value="<?= htmlspecialchars($service['price']) ?>">
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Event Date</label>
                                    <input type="date" name="event_date" required
                                           min="<?= date('Y-m-d') ?>"
                                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                </div>
                                
                                <?php if ($service['category'] === 'catering'): ?>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Guest Count</label>
                                    <input type="number" name="guest_count" id="guestCount" min="1" value="50" required
                                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                </div>
                                
                                <?php if (!empty($service['is_fixed_thali']) && $service['is_fixed_thali'] == 1): ?>
                                    <!-- Fixed Thali - Already handled above -->
                                <?php else: ?>
                                    <!-- Regular Catering - Dish Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-stone-700 mb-1">Select Dishes</label>
                                        <div class="space-y-2 max-h-48 overflow-y-auto border border-stone-200 rounded-lg p-3">
                                            <?php
                                            // Sample dishes - in real implementation, these would come from database
                                            $sample_dishes = [
                                                ['name' => 'Traditional Thali', 'price' => 350],
                                                ['name' => 'Punjabi Thali', 'price' => 400],
                                                ['name' => 'South Indian Thali', 'price' => 300],
                                                ['name' => 'Chinese Combo', 'price' => 250],
                                                ['name' => 'Continental Meal', 'price' => 450]
                                            ];
                                            
                                            foreach ($sample_dishes as $index => $dish):
                                            ?>
                                            <div class="flex items-center justify-between p-2 hover:bg-stone-50 rounded">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" name="selected_dishes[]" value="<?= $dish['price'] ?>" 
                                                           class="dish-checkbox mr-3 text-rose-600 focus:ring-rose-500"
                                                           data-dish-name="<?= htmlspecialchars($dish['name']) ?>"
                                                           onchange="updateCalculation()">
                                                    <span class="text-sm"><?= htmlspecialchars($dish['name']) ?></span>
                                                </label>
                                                <span class="text-sm font-medium text-stone-700">Rs.<?= number_format($dish['price']) ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Real-time Calculation Display -->
                                    <div class="bg-rose-50 border border-rose-200 rounded-lg p-4">
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-stone-600">Selected Dishes:</span>
                                                <span id="selectedDishesCount" class="font-medium text-stone-800">0</span>
                                            </div>
                                            <div>
                                                <span class="text-stone-600">Per Person:</span>
                                                <span id="perPersonPrice" class="font-medium text-stone-800">Rs.0</span>
                                            </div>
                                            <div>
                                                <span class="text-stone-600">Guests:</span>
                                                <span id="guestCountDisplay" class="font-medium text-stone-800">50</span>
                                            </div>
                                            <div>
                                                <span class="text-stone-600">Total Cost:</span>
                                                <span id="totalCost" class="font-bold text-rose-700 text-lg">Rs.0</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php else: ?>
                                <input type="hidden" name="guest_count" value="1">
                                <?php endif; ?>
                                
                                <button type="submit" 
                                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-lg transition">
                                    Book Now
                                </button>
                            </form>

                            <div class="mt-4 text-center">
                                <a href="<?= BASE_URL ?>customer/dashboard.php" 
                                   class="text-rose-600 hover:text-rose-700 text-sm font-medium">
                                    ← Back to Services
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gallery Section -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8">
            <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Service Gallery</h2>
            
            <?php if (!empty($gallery_images)): ?>
            <div class="gallery-grid">
                <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition"
                     onclick="openLightbox('<?= UPLOADS_URL . 'gallery/' . htmlspecialchars($image['image_path']) ?>')">
                    <img src="<?= UPLOADS_URL . 'gallery/' . htmlspecialchars($image['image_path']) ?>" 
                         alt="<?= htmlspecialchars($image['caption'] ?? 'Service image') ?>"
                         class="w-full h-full object-cover">
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Royal Empty Gallery Message -->
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-100 to-rose-100 rounded-full mb-6">
                    <span class="text-3xl">👑</span>
                </div>
                <h3 class="heading text-xl font-bold text-stone-800 mb-3">No Gallery Images Yet</h3>
                <p class="text-stone-600 max-w-md mx-auto mb-6">
                    This provider hasn't uploaded any event images yet. Check back soon to see beautiful wedding moments from their recent events.
                </p>
                <div class="flex items-center justify-center space-x-2 text-amber-600">
                    <span class="text-sm">✨</span>
                    <span class="text-sm font-medium">Gallery Coming Soon</span>
                    <span class="text-sm">✨</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <img id="lightbox-img" src="" alt="">
    </div>

    <script>
        function openLightbox(imageSrc) {
            document.getElementById('lightbox-img').src = imageSrc;
            document.getElementById('lightbox').style.display = 'block';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
        }

        // Close lightbox with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // Real-time calculation for fixed thali
        <?php if ($service['category'] === 'catering' && !empty($service['is_fixed_thali']) && $service['is_fixed_thali'] == 1): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const guestCountInput = document.getElementById('guestCount');
            const totalPriceElement = document.getElementById('totalPrice');
            const perPersonPrice = <?= $service['thali_price_per_person'] ?>;
            
            function updateTotalPrice() {
                const guestCount = parseInt(guestCountInput.value) || 1;
                const totalPrice = guestCount * perPersonPrice;
                totalPriceElement.textContent = totalPrice.toLocaleString('en-IN');
            }
            
            // Update total when guest count changes
            guestCountInput.addEventListener('input', updateTotalPrice);
            guestCountInput.addEventListener('change', updateTotalPrice);
            
            // Initial calculation
            updateTotalPrice();
        });
        <?php endif; ?>
        
        // Real-time calculation for dish selection
        <?php if ($service['category'] === 'catering' && (empty($service['is_fixed_thali']) || $service['is_fixed_thali'] != 1)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const guestCountInput = document.getElementById('guestCount');
            const selectedDishesCount = document.getElementById('selectedDishesCount');
            const perPersonPriceElement = document.getElementById('perPersonPrice');
            const guestCountDisplay = document.getElementById('guestCountDisplay');
            const totalCostElement = document.getElementById('totalCost');
            
            function updateCalculation() {
                const guestCount = parseInt(guestCountInput.value) || 1;
                const checkboxes = document.querySelectorAll('.dish-checkbox:checked');
                
                // Calculate per person price (average of selected dishes)
                let perPersonPrice = 0;
                if (checkboxes.length > 0) {
                    let totalDishPrice = 0;
                    checkboxes.forEach(checkbox => {
                        totalDishPrice += parseInt(checkbox.value);
                    });
                    perPersonPrice = totalDishPrice / checkboxes.length;
                }
                
                // Calculate total cost
                const totalCost = perPersonPrice * guestCount;
                
                // Update display
                selectedDishesCount.textContent = checkboxes.length;
                perPersonPriceElement.textContent = 'Rs.' + perPersonPrice.toLocaleString('en-IN');
                guestCountDisplay.textContent = guestCount;
                totalCostElement.textContent = 'Rs.' + totalCost.toLocaleString('en-IN');
                
                // Update form price field for submission
                const priceField = document.querySelector('input[name="price"]');
                if (priceField) {
                    priceField.value = totalCost;
                }
            }
            
            // Update calculation when guest count changes
            guestCountInput.addEventListener('input', updateCalculation);
            guestCountInput.addEventListener('change', updateCalculation);
            
            // Initial calculation
            updateCalculation();
        });
        <?php endif; ?>
    </script>
</body>
</html>
