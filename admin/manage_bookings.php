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
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(s.title LIKE ? OR c.name LIKE ? OR p.name LIKE ? OR c.email LIKE ?)";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $where_conditions[] = "b.status = ?";
    $params[] = $status;
}

if (!empty($category)) {
    $where_conditions[] = "s.category = ?";
    $params[] = $category;
}

if (!empty($date_from)) {
    $where_conditions[] = "b.booking_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "b.booking_date <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);

// Count total records
$count_sql = "
    SELECT COUNT(*) as total 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    LEFT JOIN users c ON b.customer_id = c.id 
    LEFT JOIN users p ON s.provider_id = p.id 
    WHERE $where_clause
";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch bookings
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, s.price as service_price,
           c.name as customer_name, c.email as customer_email, c.phone as customer_phone,
           p.name as provider_name, p.email as provider_email, p.phone as provider_phone
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    LEFT JOIN users c ON b.customer_id = c.id 
    LEFT JOIN users p ON s.provider_id = p.id 
    WHERE $where_clause
    ORDER BY b.booking_date DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Admin | Samaaroh</title>
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
                    <span class="text-2xl"></span>
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
            <h1 class="heading text-3xl font-bold text-stone-800">Manage Bookings</h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search bookings, customers, or providers..."
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
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
                    <label class="block text-sm font-medium text-stone-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" 
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" 
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        Filter
                    </button>
                    <a href="<?= BASE_URL ?>admin/manage_bookings.php" class="text-stone-600 hover:text-rose-600 font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700">Booking ID</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Service</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Customer</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Provider</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Event Date</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Amount</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Status</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    No bookings found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="font-medium text-gray-900">#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                        <div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($booking['booking_date'])) ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($booking['service_title']) ?></div>
                                            <div class="text-sm text-gray-500"><?= ucfirst($booking['category']) ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($booking['customer_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['customer_email']) ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($booking['provider_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($booking['provider_email']) ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="font-medium text-gray-900"><?= date('M d, Y', strtotime($booking['event_date'])) ?></div>
                                    </td>
                                    <td class="p-4 text-center font-medium text-gray-900">
                                        <?= !empty($booking['total_price']) ? 'Rs.' . number_format($booking['total_price'], 0) : 'N/A' ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?= $booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($booking['status'] === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                               ($booking['status'] === 'paid' ? 'bg-purple-100 text-purple-800' : 
                                               ($booking['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 font-medium text-sm" target="_blank">
                                                Invoice
                                            </a>
                                            <button onclick="updateBookingStatus(<?= $booking['id'] ?>)" 
                                                    class="text-green-600 hover:text-green-800 font-medium text-sm">
                                                Update
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
                            Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> bookings
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&category=<?= urlencode($category) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" 
                                   class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            for ($i = 1; $i <= $total_pages; $i++):
                                $active = $i === $page;
                                $class = $active ? 'bg-rose-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                            ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&category=<?= urlencode($category) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" 
                                   class="px-3 py-1 text-sm border border-gray-300 rounded-md <?= $class ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&category=<?= urlencode($category) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" 
                                   class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function updateBookingStatus(bookingId) {
            const newStatus = prompt('Enter new status (pending, confirmed, paid, completed, cancelled):');
            if (newStatus && ['pending', 'confirmed', 'paid', 'completed', 'cancelled'].includes(newStatus)) {
                window.location.href = '<?= BASE_URL ?>admin/update_booking_status.php?id=' + bookingId + '&status=' + newStatus;
            } else if (newStatus) {
                alert('Invalid status. Please use: pending, confirmed, paid, completed, or cancelled');
            }
        }
    </script>
</body>
</html>
