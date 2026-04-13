<?php
require_once '../config/config.php';

// Auth Check - Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied. Admin privileges required.", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get platform statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) as count FROM users")->fetchColumn(),
    'total_bookings' => $pdo->query("SELECT COUNT(*) as count FROM bookings")->fetchColumn(),
    'total_services' => $pdo->query("SELECT COUNT(*) as count FROM services")->fetchColumn(),
    'verified_users' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1")->fetchColumn(),
    'pending_bookings' => $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'confirmed_bookings' => $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
    'completed_bookings' => $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")->fetchColumn(),
];

// Get recent bookings
$recent_bookings = $pdo->query("
    SELECT b.*, s.title as service_title, u.name as customer_name, p.name as provider_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    JOIN users p ON s.provider_id = p.id
    ORDER BY b.id DESC
    LIMIT 10
")->fetchAll();

// Get recent users
$recent_users = $pdo->query("
    SELECT id, name, email, role, is_verified, created_at
    FROM users 
    ORDER BY id DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Samaaroh</title>
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

    <!-- Welcome Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="heading text-3xl font-bold text-stone-800">
                Admin Dashboard 👑
            </h1>
            <p class="text-stone-600 mt-2">
                Manage Samaaroh wedding platform - monitor bookings, users, and platform performance.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="<?= BASE_URL ?>admin/user_reports.php" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                User Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-rose-600 text-xl">👥</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['total_users']) ?></p>
                    <p class="text-stone-600 text-sm">Total Users</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-green-600 text-xl">📋</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['total_bookings']) ?></p>
                    <p class="text-stone-600 text-sm">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-amber-600 text-xl">🎪</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['total_services']) ?></p>
                    <p class="text-stone-600 text-sm">Total Services</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-purple-600 text-xl">👥</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['verified_users']) ?></p>
                    <p class="text-stone-600 text-sm">Verified Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-amber-50 rounded-xl border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-amber-600 text-xl">⏰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['pending_bookings']) ?></p>
                    <p class="text-stone-600 text-sm">Pending Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 rounded-xl border border-green-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-green-600 text-xl">✅</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['confirmed_bookings']) ?></p>
                    <p class="text-stone-600 text-sm">Confirmed Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="bg-stone-100 rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-stone-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-stone-600 text-xl">🎉</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= number_format($stats['completed_bookings']) ?></p>
                    <p class="text-stone-600 text-sm">Completed Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="heading text-xl font-bold text-stone-800 mb-6">Recent Bookings</h2>
            
            <?php if (empty($recent_bookings)): ?>
                <div class="text-center py-8">
                    <p class="text-stone-500">No bookings yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($recent_bookings, 0, 5) as $booking): ?>
                        <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg">
                            <div>
                                <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['service_title']) ?></p>
                                <p class="text-sm text-stone-600"><?= htmlspecialchars($booking['customer_name']) ?> → <?= htmlspecialchars($booking['provider_name']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></p>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php
                                    switch($booking['status']) {
                                        case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                        case 'pending': echo 'bg-amber-100 text-amber-800'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-stone-100 text-stone-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="heading text-xl font-bold text-stone-800 mb-6">Recent Users</h2>
            
            <?php if (empty($recent_users)): ?>
                <div class="text-center py-8">
                    <p class="text-stone-500">No users yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($recent_users, 0, 5) as $user): ?>
                        <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg">
                            <div>
                                <p class="font-medium text-stone-900"><?= htmlspecialchars($user['name']) ?></p>
                                <p class="text-sm text-stone-600"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : ($user['role'] === 'provider' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= $user['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $user['is_verified'] ? 'Verified' : 'Unverified' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-stone-900 rounded-2xl p-8 text-white">
        <h2 class="heading text-2xl font-bold text-white mb-6">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="<?= BASE_URL ?>admin/manage_users.php" 
               class="block bg-white hover:bg-stone-100 text-stone-900 p-6 rounded-xl text-center font-semibold transition">
                <span class="text-2xl mb-2">👥</span>
                <p>Manage Users</p>
            </a>
            <a href="<?= BASE_URL ?>admin/manage_services.php" 
               class="block bg-white hover:bg-stone-100 text-stone-900 p-6 rounded-xl text-center font-semibold transition">
                <span class="text-2xl mb-2">🎪</span>
                <p>Manage Services</p>
            </a>
            <a href="<?= BASE_URL ?>admin/manage_bookings.php" 
               class="block bg-white hover:bg-stone-100 text-stone-900 p-6 rounded-xl text-center font-semibold transition">
                <span class="text-2xl mb-2">📋</span>
                <p>Manage Bookings</p>
            </a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
