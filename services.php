<?php
require_once 'config/config.php';

// Fetch services with provider info
$services = $pdo->query("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email,
           COUNT(DISTINCT b.id) as booking_count
    FROM services s
    LEFT JOIN users u ON s.provider_id = u.id
    LEFT JOIN bookings b ON s.id = b.service_id
    WHERE s.is_available = 1 AND u.is_verified = 1
    GROUP BY s.id
    ORDER BY s.id DESC
")->fetchAll();

// Filter by category
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($category !== 'all' || !empty($search)) {
    $where_conditions = ["s.is_available = 1", "u.is_verified = 1"];
    $params = [];
    
    if ($category !== 'all') {
        $where_conditions[] = "s.category = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(s.title LIKE ? OR s.description LIKE ? OR u.name LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    $services = $pdo->query("
        SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email,
               COUNT(DISTINCT b.id) as booking_count
        FROM services s
        LEFT JOIN users u ON s.provider_id = u.id
        LEFT JOIN bookings b ON s.id = b.service_id
        WHERE $where_clause
        GROUP BY s.id
        ORDER BY s.id DESC
    ")->fetchAll();
}

// Get categories
$categories = $pdo->query("
    SELECT DISTINCT category, COUNT(*) as count
    FROM services s
    JOIN users u ON s.provider_id = u.id
    WHERE s.is_available = 1 AND u.is_verified = 1
    GROUP BY category
    ORDER BY count DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Services | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
        .service-card { transition: transform 0.3s, box-shadow 0.3s; }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <?php displayAlert(); ?>

    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Wedding Services</h1>
        <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
            Discover verified wedding vendors in Nadiad. From traditional Bagiwala to modern photography, find everything you need for your perfect day.
        </p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-2xl border border-stone-200 p-6 mb-8">
        <form method="GET" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" 
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                        placeholder="Search services, vendors..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div>
                    <select name="category" 
                        class="px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category'] ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                <?= ucfirst($cat['category']) ?> (<?= $cat['count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" 
                    class="bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <div class="mb-6">
        <p class="text-stone-600">
            Found <span class="font-bold text-stone-800"><?= count($services) ?></span> services
            <?= $category !== 'all' ? "in " . ucfirst($category) : "" ?>
            <?= !empty($search) ? "matching '" . htmlspecialchars($search) . "'" : "" ?>
        </p>
    </div>

    <!-- Services Grid -->
    <?php if (empty($services)): ?>
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="heading text-2xl font-bold text-stone-800 mb-2">No services found</h3>
            <p class="text-stone-600 mb-6">Try adjusting your search criteria or browse all available services.</p>
            <a href="?category=all" class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                View All Services
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $service): ?>
                <div class="service-card bg-white rounded-2xl border border-stone-200 overflow-hidden">
                    <!-- Service Header -->
                    <div class="bg-gradient-to-r from-rose-50 to-amber-50 p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="heading text-xl font-bold text-stone-800 mb-1">
                                    <?= htmlspecialchars($service['title']) ?>
                                </h3>
                                <p class="text-stone-500 text-sm"><?= ucfirst($service['category']) ?></p>
                            </div>
                            <span class="text-3xl">
                                <?php
                                $icons = [
                                    'bagiwala' => '🛺',
                                    'party-plot' => '🎪',
                                    'catering' => '🍲',
                                    'photography' => '📸',
                                    'decoration' => '🎨',
                                    'music' => '🎵'
                                ];
                                echo $icons[$service['category']] ?? '🎪';
                                ?>
                            </span>
                        </div>
                        
                        <p class="text-stone-600 text-sm mb-4">
                            <?= htmlspecialchars(substr($service['description'], 0, 120)) ?>...
                        </p>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-2xl font-bold text-stone-900">₹<?= number_format($service['price'], 0) ?></p>
                                <p class="text-stone-500 text-xs">per service</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-stone-900"><?= htmlspecialchars($service['provider_name']) ?></p>
                                <p class="text-stone-500 text-xs">⭐ 4.8 (<?= $service['booking_count'] ?> bookings)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Body -->
                    <div class="p-6">
                        <!-- Features -->
                        <div class="mb-4">
                            <?php
                            $features = explode(',', $service['features'] ?? '');
                            foreach (array_slice($features, 0, 3) as $feature):
                                $feature = trim($feature);
                                if (!empty($feature)):
                            ?>
                                <div class="flex items-center text-sm text-stone-600 mb-1">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <?= htmlspecialchars($feature) ?>
                                </div>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="border-t border-stone-200 pt-4 mb-4">
                            <div class="text-sm text-stone-600">
                                <p class="mb-1">📞 <?= htmlspecialchars($service['provider_phone']) ?></p>
                                <p>📧 <?= htmlspecialchars($service['provider_email']) ?></p>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                                <form method="POST" action="<?= BASE_URL ?>customer/book_service.php">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <input type="hidden" name="price" value="<?= $service['price'] ?>">
                                    <button type="submit" 
                                        class="w-full bg-rose-600 text-white py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                                        Book Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>register.php" 
                                    class="block w-full bg-rose-600 text-white py-3 rounded-xl font-semibold text-center hover:bg-rose-700 transition">
                                    Login to Book
                                </a>
                            <?php endif; ?>
                            
                            <button onclick="window.open('tel:<?= htmlspecialchars($service['provider_phone']) ?>')" 
                                class="w-full bg-stone-200 text-stone-700 py-2 rounded-xl font-semibold hover:bg-stone-300 transition">
                                Call Provider
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
