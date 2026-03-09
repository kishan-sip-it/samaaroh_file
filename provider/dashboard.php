<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login as a service provider to access this dashboard", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// FETCH PROVIDER'S SERVICES
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE provider_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll();

// FETCH PENDING BOOKINGS (12-hour window)
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.price, u.name as customer_name, u.phone 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE s.provider_id = ? AND b.status = 'pending'
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_bookings = $stmt->fetchAll();

// FETCH ALL BOOKINGS (for history)
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.price, u.name as customer_name 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE s.provider_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$all_bookings = $stmt->fetchAll();

// Calculate confirmed bookings count (PHP 7.0+ compatible)
$confirmed_count = 0;
foreach ($all_bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $confirmed_count++;
    }
}

// Calculate total earnings (PHP 7.0+ compatible)
$total_earnings = 0;
foreach ($all_bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $total_earnings += $booking['total_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .service-card { transition: transform 0.3s, box-shadow 0.3s; }
        .service-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .booking-card { border-left: 4px solid #ef4444; }
        .booking-card.confirmed { border-left-color: #10b981; }
        .booking-card.completed { border-left-color: #8b5cf6; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .urgent { animation: pulse 2s infinite; background-color: #fef2f2; }
    </style>
    <style>
/* Minimal fallback styles for offline demo */
body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
.btn { background: #e53e3e; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; }
.card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.alert { padding: 12px; border-radius: 4px; margin: 15px 0; }
.alert-error { background: #fee; border-left: 4px solid #c53030; color: #c53030; }
.alert-success { background: #efe; border-left: 4px solid #38a169; color: #38a169; }
</style>
</head>
<body class="bg-stone-50 min-h-screen">

    <?php include '../includes/navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php displayAlert(); ?>

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">Provider Dashboard</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Manage your services and booking requests from Nadiad families
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-rose-600 mb-2"><?= count($services) ?></div>
                <div class="text-stone-500 text-sm">Active Services</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-amber-600 mb-2"><?= count($pending_bookings) ?></div>
                <div class="text-stone-500 text-sm">Pending Requests</div>
                <div class="text-xs text-amber-500 mt-1">(12h window)</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-green-600 mb-2"><?= $confirmed_count ?></div>
                <div class="text-stone-500 text-sm">Confirmed Bookings</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-stone-800 mb-2">
                    ₹<?= number_format($total_earnings, 0) ?>
                </div>
                <div class="text-stone-500 text-sm">Total Earnings</div>
            </div>
        </div>

        <!-- Pending Bookings Section -->
        <section class="mb-16">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-stone-800">Pending Requests <span class="text-amber-600">(Respond within 12 hours)</span></h2>
                <a href="#all-bookings" class="text-rose-600 text-sm font-medium hover:underline hidden md:block">View All Bookings →</a>
            </div>
            
            <?php if (empty($pending_bookings)): ?>
                <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                    <div class="text-stone-300 text-5xl mb-4">📭</div>
                    <p class="text-stone-500">No pending booking requests</p>
                    <p class="text-stone-400 text-sm mt-2">You'll receive notifications when customers book your services</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending_bookings as $booking): 
                        // Calculate time remaining (12 hours from booking)
                        $booking_time = strtotime($booking['booking_date']);
                        $current_time = time();
                        $time_diff = ($booking_time + 43200) - $current_time; // 43200 seconds = 12 hours
                        $hours = floor($time_diff / 3600);
                        $minutes = floor(($time_diff % 3600) / 60);
                        $is_urgent = $time_diff < 7200; // Less than 2 hours
                    ?>
                        <div class="booking-card bg-white rounded-2xl border border-stone-200 p-6 <?= $is_urgent ? 'urgent' : '' ?>">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">
                                            PENDING
                                        </span>
                                        <?php if ($is_urgent): ?>
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-medium flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                URGENT
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($booking['service_title']) ?></h3>
                                    <p class="text-stone-500 mt-1">
                                        <span class="font-medium"><?= htmlspecialchars($booking['customer_name']) ?></span> • 
                                        <span><?= htmlspecialchars($booking['phone'] ?? 'N/A') ?></span>
                                    </p>
                                    <div class="mt-3 flex items-center text-sm text-stone-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Wedding Date: <span class="font-medium ml-1"><?= date('M d, Y', strtotime($booking['event_date'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end w-full md:w-auto">
                                    <div class="font-bold text-rose-600 text-xl">₹<?= number_format($booking['total_price'], 0) ?></div>
                                    <div class="text-xs text-stone-400 mt-1">
                                        Requested: <?= date('M d, Y h:i A', strtotime($booking['booking_date'])) ?>
                                    </div>
                                    
                                    <?php if ($time_diff > 0): ?>
                                        <div class="mt-3 bg-amber-50 text-amber-800 px-3 py-1.5 rounded-lg font-medium text-sm">
                                            <span class="font-bold"><?= $hours ?></span>h <span class="font-bold"><?= $minutes ?></span>m left
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-3 bg-red-50 text-red-800 px-3 py-1.5 rounded-lg font-medium text-sm">
                                            EXPIRED
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-4 flex gap-2 w-full md:w-auto">
                                        <form method="POST" action="<?= BASE_URL ?>provider/update_booking.php" class="w-full md:w-auto">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>provider/update_booking.php" class="w-full md:w-auto">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Services Section -->
        <section class="mb-16">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-stone-800">My Services</h2>
                <a href="<?= BASE_URL ?>provider/add_service.php" class="bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-700 transition flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Service
                </a>
            </div>
            
            <?php if (empty($services)): ?>
                <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                    <div class="text-stone-300 text-5xl mb-4">✨</div>
                    <p class="text-stone-500">You haven't added any services yet</p>
                    <a href="<?= BASE_URL ?>provider/add_service.php" class="mt-4 inline-block bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-700 transition">
                        Add Your First Service
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card bg-white rounded-2xl overflow-hidden border border-stone-200">
                            <div class="h-40 bg-stone-100 relative">
                                <?php if (!empty($service['image_path'])): ?>
                                    <img src="<?= UPLOADS_URL ?><?= htmlspecialchars($service['image_path']) ?>"     
                                         alt="<?= htmlspecialchars($service['title']) ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-50 to-amber-50">
                                        <span class="text-5xl">
                                            <?php 
                                            $icons = [
                                                'das_bagiwala' => '🛺',
                                                'party_plot' => '🎪',
                                                'catering' => '🍲',
                                                'photography' => '📸',
                                                'decor' => '🎨',
                                                'entertainment' => '🎤'
                                            ];
                                            echo $icons[$service['category']] ?? '✨';
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute top-3 left-3">
                                    <span class="px-2 py-1 bg-<?= $service['tier'] === 'standard' ? 'blue' : ($service['tier'] === 'premium' ? 'amber' : 'rose') ?>-100 text-<?= $service['tier'] === 'standard' ? 'blue' : ($service['tier'] === 'premium' ? 'amber' : 'rose') ?>-800 text-xs font-medium rounded-full">
                                        <?= ucfirst($service['tier']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($service['title']) ?></h3>
                                    <span class="font-bold text-rose-600">₹<?= number_format($service['price'], 0) ?></span>
                                </div>
                                
                                <p class="text-stone-500 text-sm mb-4 line-clamp-2">
                                    <?= htmlspecialchars(substr($service['description'], 0, 80)) ?>...
                                </p>
                                
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs bg-stone-100 text-stone-700 px-2 py-1 rounded-full">
                                        <?= ucfirst($service['category']) ?>
                                    </span>
                                    <span class="text-xs <?= $service['is_available'] ? 'text-green-600' : 'text-red-600' ?> font-medium">
                                        <?= $service['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </div>
                                
                                <div class="flex gap-2">
                                    <a href="<?= BASE_URL ?>provider/edit_service.php?id=<?= $service['id'] ?>" 
                                       class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-2 rounded-lg text-center text-sm transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>provider/delete_service.php" class="flex-1">
                                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                        <button type="submit" 
                                                class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 rounded-lg text-center text-sm transition"
                                                onclick="return confirm('Are you sure you want to delete this service?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- All Bookings Section -->
        <section id="all-bookings">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-stone-800">Booking History</h2>
            </div>
            
            <?php if (empty($all_bookings)): ?>
                <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                    <p class="text-stone-500">No bookings yet</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200">
                            <thead class="bg-stone-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-stone-200">
                                <?php foreach ($all_bookings as $booking): ?>
                                    <tr class="<?= $booking['status'] === 'confirmed' ? 'bg-green-50' : ($booking['status'] === 'completed' ? 'bg-purple-50' : '') ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-stone-900"><?= htmlspecialchars($booking['service_title']) ?></div>
                                            <div class="text-xs text-stone-500"><?= ucfirst($booking['category'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-stone-900"><?= htmlspecialchars($booking['customer_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-500">
                                            <?= date('M d, Y', strtotime($booking['event_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-rose-600">
                                            ₹<?= number_format($booking['total_price'], 0) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full
                                                <?= $booking['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 
                                                   ($booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                   ($booking['status'] === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800')) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Real-time Timer Script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Update timers every minute
        setInterval(() => {
            document.querySelectorAll('.urgent').forEach(card => {
                const timeLeftEl = card.querySelector('.bg-amber-50');
                if (timeLeftEl) {
                    const [hours, minutes] = timeLeftEl.textContent.trim().split('h');
                    let h = parseInt(hours);
                    let m = parseInt(minutes);
                    
                    if (m > 0) m--;
                    else if (h > 0) { h--; m = 59; }
                    else { timeLeftEl.textContent = 'EXPIRED'; return; }
                    
                    timeLeftEl.innerHTML = `<span class="font-bold">${h}</span>h <span class="font-bold">${m}</span>m left`;
                }
            });
        }, 60000);
    });
    </script>
</body>
</html>