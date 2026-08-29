<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access your dashboard", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Fetch user bookings
$bookings = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, u.name as provider_name,
           s.price as service_price, b.status as booking_status
    FROM bookings b
    LEFT JOIN services s ON b.service_id = s.id
    LEFT JOIN users u ON s.provider_id = u.id
    WHERE b.customer_id = ?
    ORDER BY b.id DESC
");
$bookings->execute([$_SESSION['user_id']]);
$bookings_list = $bookings->fetchAll();

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
        SELECT s.*, u.name as provider_name, u.phone as provider_phone,
               COUNT(DISTINCT b.id) as booking_count
        FROM services s
        LEFT JOIN users u ON s.provider_id = u.id
        LEFT JOIN bookings b ON s.id = b.service_id
        WHERE $where_clause
        GROUP BY s.id
        ORDER BY s.id DESC
    ")->fetchAll();
} else {
    // Fetch available services
    $services = $pdo->query("
        SELECT s.*, u.name as provider_name, u.phone as provider_phone,
               COUNT(DISTINCT b.id) as booking_count
        FROM services s
        JOIN users u ON s.provider_id = u.id
        LEFT JOIN bookings b ON s.id = b.service_id
        WHERE s.is_available = 1 AND u.is_verified = 1
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
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include '../includes/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php displayAlert(); ?>

    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="heading text-3xl font-bold text-stone-800">
            Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! 
        </h1>
        <p class="text-stone-600 mt-2">
            Manage your wedding bookings and discover new services for your special day.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-rose-600 text-xl">📋</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= count($bookings_list) ?></p>
                    <p class="text-stone-600 text-sm">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-green-600 text-xl">✅</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        <?= count(array_filter($bookings_list, function($b) { return $b['booking_status'] === 'confirmed'; })) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Confirmed</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-amber-600 text-xl">⏰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        <?= count(array_filter($bookings_list, function($b) { return $b['booking_status'] === 'pending'; })) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Pending</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-blue-600 text-xl">💰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        ₹<?= number_format(array_sum(array_column($bookings_list, 'total_price')), 0) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Total Spent</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Services Section -->
    <section class="mb-12">
    <div>
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Available Services</h2>
        
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
        
        <?php if (empty($services)): ?>
            <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
                <div class="text-6xl mb-4">????</div>
                <h3 class="heading text-2xl font-bold text-stone-800 mb-2">No services found</h3>
                <p class="text-stone-600 mb-6">Try adjusting your search criteria or browse all available services.</p>
                <a href="?category=all" class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                    View All Services
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($services, 0, 6) as $index => $service): ?>
                <div class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-lg transition <?= $service['category'] === 'photography' ? 'order-first' : '' ?>">
                    <!-- Banner Image -->
                    <div class="relative h-48 bg-stone-100">
                        <?php if (!empty($service['image_path'])): ?>
                            <img src="<?= UPLOADS_URL . htmlspecialchars($service['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($service['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-100 to-stone-100">
                                <span class="text-4xl">
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
                        <div class="absolute top-3 left-3">
                            <span class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-stone-700">
                                <?= ucfirst(htmlspecialchars($service['category'])) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-stone-800 mb-1"><?= htmlspecialchars($service['title']) ?></h3>
                            <p class="text-stone-600 text-sm"><?= htmlspecialchars(substr($service['description'], 0, 100)) ?>...</p>
                        </div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <p class="text-2xl font-bold text-stone-900">₹<?= number_format($service['price'], 0) ?></p>
                                <p class="text-stone-500 text-xs">per service</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-stone-900"><?= htmlspecialchars($service['provider_name']) ?></p>
                                <p class="text-stone-500 text-xs">⭐ 4.8 (23 reviews)</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 mb-4">
                            <a href="<?= BASE_URL ?>customer/service_detail.php?id=<?= $service['id'] ?>" 
                               class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 py-2 px-3 rounded-lg text-sm font-medium transition text-center">
                                View Gallery
                            </a>
                        </div>
                    
                    <form method="POST" action="<?= BASE_URL ?>customer/book_service.php" class="space-y-3">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                        <input type="hidden" name="price" value="<?= $service['price'] ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Event Date</label>
                            <input type="date" name="event_date" required
                                class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm">
                        </div>
                        
                        <?php if ($service['category'] === 'catering'): ?>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Guest Count</label>
                            <input type="number" name="guest_count" min="1" value="50" required
                                class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm">
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="guest_count" value="1">
                        <?php endif; ?>
                        
                        <button type="submit" class="w-full bg-rose-600 text-white py-2 rounded-lg font-semibold hover:bg-rose-700 transition">
                            Book Now
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        </div>
    </section>

    <!-- Recent Bookings Section -->
    <section class="mb-8">
    <div class="mb-8">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Recent Bookings</h2>
        
        <?php if (empty($bookings_list)): ?>
            <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="font-bold text-xl text-stone-800 mb-2">No bookings yet</h3>
                <p class="text-stone-600 mb-6">Start planning your wedding by booking our amazing services!</p>
                <a href="<?= BASE_URL ?>services.php" class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-700 transition">
                    Browse Services
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-stone-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200">
                            <?php foreach (array_slice($bookings_list, 0, 5) as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['service_title']) ?></p>
                                            <p class="text-sm text-stone-500"><?= htmlspecialchars($booking['category']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['provider_name']) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-stone-900"><?= date('M j, Y') ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php
                                            switch($booking['booking_status']) {
                                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                                case 'pending': echo 'bg-amber-100 text-amber-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'paid': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'completed': echo 'bg-stone-100 text-stone-800'; break;
                                                default: echo 'bg-stone-100 text-stone-800';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $booking['booking_status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                            <a href="<?= BASE_URL ?>customer/booking_payment.php?booking_id=<?= $booking['id'] ?>" 
                                               class="text-rose-600 hover:text-rose-900 font-medium">
                                                Make Payment
                                            </a>
                                        <?php elseif ($booking['booking_status'] === 'paid'): ?>
                                            <a href="<?= BASE_URL ?>customer/booking_payment.php?booking_id=<?= $booking['id'] ?>" 
                                               class="text-rose-600 hover:text-rose-900 font-medium">
                                                Final Payment
                                            </a>
                                        <?php else: ?>
                                            <a href="#" class="text-rose-600 hover:text-rose-900 font-medium">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($bookings_list) > 5): ?>
                    <div class="px-6 py-4 bg-stone-50 border-t border-stone-200">
                        <a href="#" class="text-rose-600 hover:text-rose-900 font-medium">View all bookings →</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
