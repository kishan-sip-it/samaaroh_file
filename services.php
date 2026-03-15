<?php
require_once 'config/config.php';

// FETCH SERVICES WITH PROVIDER INFO
$services = $pdo->query("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email,
           COUNT(DISTINCT b.id) as booking_count
    FROM services s
    LEFT JOIN users u ON s.provider_id = u.id
    LEFT JOIN bookings b ON s.id = b.service_id
    WHERE s.is_available = 1 AND u.is_verified = 1
    GROUP BY s.id
    ORDER BY s.created_at DESC
")->fetchAll();

// FILTER BY CATEGORY
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
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("
        SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email,
               COUNT(DISTINCT b.id) as booking_count
        FROM services s
        LEFT JOIN users u ON s.provider_id = u.id
        LEFT JOIN bookings b ON s.id = b.service_id
        $where_clause
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute($params);
    $services = $stmt->fetchAll();
}

// GET CATEGORIES FOR FILTER
$categories = [
    'photography' => 'Photography',
    'catering' => 'Catering',
    'decoration' => 'Decoration',
    'venue' => 'Venue',
    'das-bagiwala' => 'Das Bagiwala',
    'entertainment' => 'Entertainment',
    'makeup' => 'Makeup & Beauty',
    'transport' => 'Transport',
    'other' => 'Other'
];
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
        .service-card { transition: transform 0.3s, box-shadow 0.3s; }
        .service-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15); }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-rose-600 to-amber-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold text-white mb-4">
                Wedding Services in Nadiad
            </h1>
            <p class="text-xl text-white/90 max-w-2xl mx-auto">
                Discover verified wedding vendors for your perfect Gujarati wedding celebration
            </p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php displayAlert(); ?>
        
        <!-- Search and Filters -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 mb-8 shadow-sm">
            <form method="GET" class="space-y-4">
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-stone-700 mb-2">Search Services</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>"
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                               placeholder="Search services, providers...">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-stone-700 mb-2">Category</label>
                        <select id="category" name="category" 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                            <?php foreach ($categories as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $category === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-3 px-6 rounded-lg transition">
                            Search Services
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Summary -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-stone-800">
                    <?= count($services) ?> Services Available
                </h2>
                <?php if ($category !== 'all' || !empty($search)): ?>
                <p class="text-stone-600 mt-1">
                    <?= $category !== 'all' ? 'Category: ' . $categories[$category] : '' ?>
                    <?= !empty($search) ? ' | Search: "' . htmlspecialchars($search) . '"' : '' ?>
                </p>
                <?php endif; ?>
            </div>
            
            <?php if ($category !== 'all' || !empty($search)): ?>
            <a href="services.php" class="text-rose-600 hover:text-rose-700 font-medium">
                Clear Filters
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Services Grid -->
        <?php if (empty($services)): ?>
            <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
                <div class="text-stone-300 text-6xl mb-4">🔍</div>
                <h3 class="text-xl font-bold text-stone-800 mb-2">No Services Found</h3>
                <p class="text-stone-500 mb-6">
                    <?php if (!empty($search) || $category !== 'all'): ?>
                        Try adjusting your search criteria or browse all categories.
                    <?php else: ?>
                        No services are currently available. Check back soon!
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || $category !== 'all'): ?>
                <a href="services.php" class="inline-block bg-rose-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-rose-700 transition">
                    View All Services
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $service): ?>
                <div class="service-card bg-white rounded-2xl border border-stone-200 overflow-hidden">
                    <!-- Service Image -->
                    <div class="h-48 bg-gradient-to-br from-rose-100 to-amber-100 relative">
                        <?php if ($service['image_url']): ?>
                        <img src="<?= BASE_URL . $service['image_url'] ?>" 
                             alt="<?= htmlspecialchars($service['title']) ?>"
                             class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="text-4xl text-rose-300">
                                <?= $service['category'] === 'photography' ? '📸' : 
                                   ($service['category'] === 'catering' ? '🍽️' : 
                                   ($service['category'] === 'decoration' ? '🎨' : 
                                   ($service['category'] === 'venue' ? '🏢' : 
                                   ($service['category'] === 'das-bagiwala' ? '🐪' : 
                                   ($service['category'] === 'entertainment' ? '🎭' : 
                                   ($service['category'] === 'makeup' ? '💄' : 
                                   ($service['category'] === 'transport' ? '🚗' : '📦'))))))) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tier Badge -->
                        <div class="absolute top-4 right-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                <?= $service['tier'] === 'basic' ? 'bg-green-100 text-green-700' : 
                                   ($service['tier'] === 'premium' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700') ?>">
                                <?= ucfirst($service['tier']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Service Details -->
                    <div class="p-6">
                        <div class="mb-3">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-rose-100 text-rose-700">
                                <?= $categories[$service['category']] ?? $service['category'] ?>
                            </span>
                        </div>
                        
                        <h3 class="font-bold text-lg text-stone-800 mb-2">
                            <?= htmlspecialchars($service['title']) ?>
                        </h3>
                        
                        <p class="text-stone-600 text-sm mb-4 line-clamp-2">
                            <?= htmlspecialchars($service['description']) ?>
                        </p>
                        
                        <!-- Provider Info -->
                        <div class="mb-4 pb-4 border-b border-stone-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-stone-800"><?= htmlspecialchars($service['provider_name']) ?></p>
                                    <p class="text-xs text-stone-500">
                                        <?= $service['booking_count'] ?> bookings
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-rose-600">₹<?= number_format($service['price'], 0) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($_SESSION['role'] === 'customer'): ?>
                                    <a href="<?= BASE_URL ?>customer/book_service.php?id=<?= $service['id'] ?>" 
                                       class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 px-4 rounded-lg transition text-center block">
                                        Book This Service
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>login.php?redirect=services.php" 
                                       class="w-full bg-stone-200 text-stone-600 font-medium py-2 px-4 rounded-lg transition text-center block">
                                        Login as Customer to Book
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>register.php" 
                                   class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 px-4 rounded-lg transition text-center block">
                                    Register to Book
                                </a>
                            <?php endif; ?>
                            
                            <a href="#" onclick="showProviderDetails(<?= $service['provider_id'] ?>)" 
                               class="w-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-2 px-4 rounded-lg transition text-center block">
                                View Provider Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Category Pills -->
        <div class="mt-12 bg-white rounded-2xl border border-stone-200 p-6">
            <h3 class="font-bold text-lg text-stone-800 mb-4">Browse by Category</h3>
            <div class="flex flex-wrap gap-2">
                <a href="services.php?category=all" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition
                          <?= $category === 'all' ? 'bg-rose-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
                    All Services
                </a>
                <?php foreach ($categories as $value => $label): ?>
                <a href="services.php?category=<?= $value ?>" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition
                          <?= $category === $value ? 'bg-rose-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        function showProviderDetails(providerId) {
            // Simple alert for now - can be enhanced with modal
            alert('Provider details would be shown here. Provider ID: ' + providerId);
        }
    </script>
</body>
</html>
