<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(s.title LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $where_conditions[] = "s.category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $where_conditions[] = "s.is_available = ?";
    $params[] = $status === 'active' ? 1 : 0;
}

$where_clause = implode(' AND ', $where_conditions);

// Count total records
$count_sql = "
    SELECT COUNT(*) as total 
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE $where_clause
";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch services
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.email as provider_email, u.phone as provider_phone
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE $where_clause
    ORDER BY s.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services | Admin | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🎊</span>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="heading text-xl font-bold tracking-tight text-rose-700">SAMAAROH ADMIN</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium">Dashboard</a>
                    <a href="<?= BASE_URL ?>admin/reports.php" class="text-stone-600 hover:text-rose-600 font-medium">Reports</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="heading text-3xl font-bold text-stone-800">Manage Services</h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search services, providers, or emails..."
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="">All Categories</option>
                        <option value="catering" <?= $category === 'catering' ? 'selected' : '' ?>>Catering</option>
                        <option value="photography" <?= $category === 'photography' ? 'selected' : '' ?>>Photography</option>
                        <option value="decoration" <?= $category === 'decoration' ? 'selected' : '' ?>>Decoration</option>
                        <option value="music" <?= $category === 'music' ? 'selected' : '' ?>>Music</option>
                        <option value="venue" <?= $category === 'venue' ? 'selected' : '' ?>>Venue</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-3">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        Filter
                    </button>
                    <a href="<?= BASE_URL ?>admin/manage_services.php" class="inline-flex items-center px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white font-medium rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Services Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700">Service</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Provider</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Category</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Price</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Status</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    No services found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($service['image'])): ?>
                                                <img src="<?= UPLOADS_URL . $service['image'] ?>" alt="<?= htmlspecialchars($service['title']) ?>" 
                                                     class="w-12 h-12 rounded-lg object-cover">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <span class="text-gray-400 text-2xl">📷</span>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium text-gray-900"><?= htmlspecialchars($service['title']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($service['provider_name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?= $service['category'] === 'catering' ? 'bg-orange-100 text-orange-800' : 
                                               ($service['category'] === 'photography' ? 'bg-blue-100 text-blue-800' : 
                                               ($service['category'] === 'decoration' ? 'bg-purple-100 text-purple-800' : 
                                               ($service['category'] === 'music' ? 'bg-green-100 text-green-800' : 
                                               ($service['category'] === 'venue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')))) ?>">
                                            <?= ucfirst($service['category']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center font-medium text-gray-900">
                                        ₹<?= number_format($service['price'], 0) ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?= $service['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $service['is_available'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= BASE_URL ?>admin/edit_service.php?id=<?= $service['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                Edit
                                            </a>
                                            <button onclick="toggleServiceStatus(<?= $service['id'] ?>, <?= $service['is_available'] ? 0 : 1 ?>)" 
                                                    class="text-<?= $service['is_available'] ? 'red-600 hover:text-red-800' : 'green-600 hover:text-green-800' ?> font-medium text-sm">
                                                <?= $service['is_available'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-gray-50 px-4 py-3 border-t">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> services
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" 
                                   class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            for ($i = 1; $i <= $total_pages; $i++):
                                $active = $i === $page;
                                $class = $active ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                            ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" 
                                   class="px-3 py-1 text-sm border border-gray-300 rounded-md <?= $class ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>" 
                                   class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleServiceStatus(serviceId, newStatus) {
            if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this service?')) {
                window.location.href = '<?= BASE_URL ?>admin/toggle_service_status.php?id=' + serviceId + '&status=' + newStatus;
            }
        }
    </script>
</body>
</html>
