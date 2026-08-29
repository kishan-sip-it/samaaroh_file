<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to view your bookings", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// FETCH ALL BOOKINGS WITH PROVIDER & SERVICE DETAILS
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, s.price as service_price, 
           u.name as provider_name, u.phone as provider_phone
    FROM bookings b
    LEFT JOIN services s ON b.service_id = s.id
    LEFT JOIN users u ON s.provider_id = u.id
    WHERE b.customer_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

// STATS CALCULATION (PHP 7.0+ COMPATIBLE)
$total_bookings = count($bookings);
$pending_count = 0;
$confirmed_count = 0;
$paid_count = 0;
$cancelled_count = 0;

foreach ($bookings as $booking) {
    if ($booking['status'] === 'pending') $pending_count++;
    elseif ($booking['status'] === 'confirmed') $confirmed_count++;
    elseif ($booking['status'] === 'paid') $paid_count++;
    elseif ($booking['status'] === 'cancelled') $cancelled_count++;
}

// Handle donation flash message
$show_donation_message = false;
if (isset($_SESSION['show_donation_message']) && $_SESSION['show_donation_message']) {
    $show_donation_message = true;
    unset($_SESSION['show_donation_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .booking-card { transition: transform 0.3s, box-shadow 0.3s; }
        .booking-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .status-pending { border-left: 4px solid #f59e0b; }
        .status-confirmed { border-left: 4px solid #10b981; }
        .status-cancelled { border-left: 4px solid #ef4444; }
        .status-completed { border-left: 4px solid #8b5cf6; }
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

    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-50 border-b border-stone-200">
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
        <!-- Compact Professional Flash Message -->
        <?php if ($show_donation_message): ?>
        <div id="donationMessage" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg z-50 rounded-lg px-6 py-3 flex items-center gap-3 transition-all duration-300">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-xs">🇮🇳</span>
                </div>
                <div>
                    <div class="font-semibold text-sm">Payment Successful • Booking Secured</div>
                    <div class="text-xs opacity-90">1% contributed to Indian Army Welfare Fund</div>
                </div>
            </div>
            <button onclick="closeDonationMessage()" class="ml-2 text-white/70 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <script>
        // Compact auto-hide with smooth fade
        setTimeout(() => {
            const message = document.getElementById('donationMessage');
            if (message) {
                message.style.opacity = '0';
                message.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => message.remove(), 300);
            }
        }, 6000);
        
        function closeDonationMessage() {
            const message = document.getElementById('donationMessage');
            if (message) {
                message.style.opacity = '0';
                message.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => message.remove(), 200);
            }
        }
        </script>
        <?php endif; ?>
        
        <?php displayAlert(); ?>

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">My Wedding Bookings</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Track all your service bookings for your upcoming wedding in Nadiad
            </p>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-rose-600 mb-2"><?= $total_bookings ?></div>
                <div class="text-stone-500 text-sm">Total Bookings</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-amber-600 mb-2"><?= $pending_count ?></div>
                <div class="text-stone-500 text-sm">Pending (12h window)</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-green-600 mb-2"><?= $confirmed_count ?></div>
                <div class="text-stone-500 text-sm">Confirmed</div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="text-3xl font-bold text-red-600 mb-2"><?= $cancelled_count ?></div>
                <div class="text-stone-500 text-sm">Cancelled</div>
            </div>
        </div>

        <!-- Bookings List -->
        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
                <div class="text-stone-300 text-6xl mb-4">📭</div>
                <h3 class="text-xl font-bold text-stone-800 mb-2">No Bookings Yet</h3>
                <p class="text-stone-500 mb-6">
                    You haven't booked any services yet. Start planning your wedding!
                </p>
                <a href="<?= BASE_URL ?>customer/dashboard.php" class="inline-block bg-rose-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-rose-700 transition">
                    Browse Services
                </a>
            </div>
        <?php else: ?>
            <!-- Compact View (First 5 bookings) -->
            <div class="space-y-6 mb-6">
                <?php 
                $display_bookings = array_slice($bookings, 0, 5);
                foreach ($display_bookings as $booking): 
                ?>
                    <div class="booking-card bg-white rounded-2xl border border-stone-200 p-6 <?= $booking['status'] === 'pending' ? 'status-pending' : ($booking['status'] === 'confirmed' ? 'status-confirmed' : ($booking['status'] === 'cancelled' ? 'status-cancelled' : 'status-completed')) ?>">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        <?= $booking['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 
                                           ($booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                           ($booking['status'] === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800')) ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                            12-hour window
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="font-bold text-lg text-stone-800 mb-1">
                                    <?= htmlspecialchars($booking['service_title'] ?? 'Service Booking') ?>
                                </h3>
                                
                                <?php if (!empty($booking['provider_name'])): ?>
                                    <div class="text-stone-500 text-sm mb-2">
                                        <span class="font-medium"><?= htmlspecialchars($booking['provider_name']) ?></span>
                                        <?php if (!empty($booking['provider_phone'])): ?>
                                            <span class="mx-2">•</span>
                                            <span><?= htmlspecialchars($booking['provider_phone']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-stone-100 text-sm">
                                    <div>
                                        <div class="text-stone-400">Wedding Date</div>
                                        <div class="font-medium text-stone-800">
                                            <?= date('M d, Y', strtotime($booking['event_date'])) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-stone-400">Booked On</div>
                                        <div class="font-medium text-stone-800">
                                            <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-stone-400">Category</div>
                                        <div class="font-medium text-stone-800 capitalize">
                                            <?= htmlspecialchars($booking['category'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-stone-400">Amount</div>
                                        <div class="font-bold text-rose-600 text-lg">
                                            ₹<?= number_format($booking['total_price'], 0) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end w-full md:w-auto">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="bg-amber-50 text-amber-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                        Waiting for provider acceptance
                                    </div>
                                    <a href="<?= BASE_URL ?>customer/dashboard.php#services" class="text-rose-600 text-sm font-medium hover:underline">
                                        Browse more services →
                                    </a>
                                <?php elseif ($booking['status'] === 'accepted'): ?>
                                    <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                        ✅ Accepted by provider - Pay advance to confirm
                                    </div>
                                    <a href="<?= BASE_URL ?>customer/pay_advance.php?id=<?= $booking['id'] ?>" 
                                       class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                        </svg>
                                        Pay 40% Advance
                                    </a>
                                <?php elseif ($booking['status'] === 'confirmed'): ?>
                                    <?php if (!empty($booking['advance_amount']) && $booking['advance_amount'] > 0): ?>
                                        <div class="bg-purple-50 text-purple-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                            ✅ Advance Paid - Wedding Date Locked
                                        </div>
                                        <div class="flex gap-2 mb-2">
                                            <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=advance" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                                </svg>
                                                View Invoice
                                            </a>
                                            <a href="<?= BASE_URL ?>customer/pay_final.php?id=<?= $booking['id'] ?>" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                                </svg>
                                                Pay Final 60%
                                            </a>
                                        </div>
                                        <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                            Contact provider for details →
                                        </a>
                                    <?php else: ?>
                                        <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                            ✅ Confirmed by provider - Pay advance to lock date
                                        </div>
                                        <a href="<?= BASE_URL ?>customer/pay_advance.php?id=<?= $booking['id'] ?>" 
                                           class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                            </svg>
                                            Pay 40% Advance
                                        </a>
                                    <?php endif; ?>
                                <?php elseif ($booking['status'] === 'paid'): ?>
                                    <div class="bg-purple-50 text-purple-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                        ✅ Advance Paid - Wedding Date Locked
                                    </div>
                                    <div class="flex gap-2 mb-2">
                                        <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=advance" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                            </svg>
                                            Advance Invoice
                                        </a>
                                        <a href="<?= BASE_URL ?>customer/pay_final.php?id=<?= $booking['id'] ?>" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                            </svg>
                                            Pay Final 60%
                                        </a>
                                    </div>
                                    <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                        Contact provider for details →
                                    </a>
                                <?php elseif ($booking['status'] === 'completed'): ?>
                                    <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                        ✅ Booking Completed - Fully Paid
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=final" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                            </svg>
                                            Final Invoice
                                        </a>
                                        <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                            Contact provider for details →
                                        </a>
                                    </div>
                                <?php elseif ($booking['status'] === 'cancelled'): ?>
                                    <div class="bg-red-50 text-red-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                        ❌ Cancelled
                                    </div>
                                    <a href="<?= BASE_URL ?>customer/dashboard.php#services" class="text-rose-600 text-sm font-medium hover:underline">
                                        Find alternative services →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Show All Button -->
            <?php if (count($bookings) > 5): ?>
                <div class="text-center">
                    <button onclick="openBookingDrawer()" 
                            class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Show All (<?= count($bookings) ?> Bookings)
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    <!-- Customer Booking Drawer -->
    <div id="bookingDrawer" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeBookingDrawer()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-xl transform transition-transform duration-300 translate-x-full" id="drawerContent">
            <div class="h-full flex flex-col">
                <!-- Drawer Header -->
                <div class="bg-rose-600 text-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="heading text-xl font-bold">All My Bookings (<?= count($bookings) ?>)</h2>
                        <button onclick="closeBookingDrawer()" class="text-white hover:text-rose-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="space-y-6">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="bg-stone-50 rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                                <?= $booking['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 
                                                   ($booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                   ($booking['status'] === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800')) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                                    12-hour window
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h3 class="font-bold text-lg text-stone-800 mb-1">
                                            <?= htmlspecialchars($booking['service_title'] ?? 'Service Booking') ?>
                                        </h3>
                                        
                                        <?php if (!empty($booking['provider_name'])): ?>
                                            <div class="text-stone-500 text-sm mb-2">
                                                <span class="font-medium"><?= htmlspecialchars($booking['provider_name']) ?></span>
                                                <?php if (!empty($booking['provider_phone'])): ?>
                                                    <span class="mx-2">•</span>
                                                    <span><?= htmlspecialchars($booking['provider_phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-stone-100 text-sm">
                                            <div>
                                                <div class="text-stone-400">Wedding Date</div>
                                                <div class="font-medium text-stone-800">
                                                    <?= date('M d, Y', strtotime($booking['event_date'])) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-stone-400">Booked On</div>
                                                <div class="font-medium text-stone-800">
                                                    <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-stone-400">Category</div>
                                                <div class="font-medium text-stone-800 capitalize">
                                                    <?= htmlspecialchars($booking['category'] ?? 'N/A') ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-stone-400">Amount</div>
                                                <div class="font-bold text-rose-600 text-lg">
                                                    ₹<?= number_format($booking['total_price'], 0) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-end w-full md:w-auto">
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <div class="bg-amber-50 text-amber-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                Waiting for provider acceptance
                                            </div>
                                            <a href="<?= BASE_URL ?>customer/dashboard.php#services" class="text-rose-600 text-sm font-medium hover:underline">
                                                Browse more services →
                                            </a>
                                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                                            <?php if (!empty($booking['advance_amount']) && $booking['advance_amount'] > 0): ?>
                                                <div class="bg-purple-50 text-purple-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                    Advance Paid - Wedding Date Locked
                                                </div>
                                                <div class="flex gap-2 mb-2">
                                                    <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=advance" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                                        </svg>
                                                        View Invoice
                                                    </a>
                                                    <a href="<?= BASE_URL ?>customer/pay_final.php?id=<?= $booking['id'] ?>" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                                        </svg>
                                                        Pay Final 60%
                                                    </a>
                                                </div>
                                                <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                                    Contact provider for details →
                                                </a>
                                            <?php else: ?>
                                                <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                    Confirmed by provider - Pay advance to lock date
                                                </div>
                                                <a href="<?= BASE_URL ?>customer/pay_advance.php?id=<?= $booking['id'] ?>" 
                                                   class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                                    </svg>
                                                    Pay 40% Advance
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($booking['status'] === 'paid'): ?>
                                            <div class="bg-purple-50 text-purple-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                Advance Paid - Wedding Date Locked
                                            </div>
                                            <div class="flex gap-2 mb-2">
                                                <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=advance" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                                    </svg>
                                                    Advance Invoice
                                                </a>
                                                <a href="<?= BASE_URL ?>customer/pay_final.php?id=<?= $booking['id'] ?>" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 11-4 0v2a2 2 0 104 0v6a2 2 0 11-4 0V9a4 4 0 118 0v6a4 4 0 01-8 0V5z" />
                                                    </svg>
                                                    Pay Final 60%
                                                </a>
                                            </div>
                                            <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                                Contact provider for details →
                                            </a>
                                        <?php elseif ($booking['status'] === 'completed'): ?>
                                            <div class="bg-green-50 text-green-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                Booking Completed - Fully Paid
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="<?= BASE_URL ?>invoice.php?id=<?= $booking['id'] ?>&type=final" class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition text-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                                                    </svg>
                                                    Final Invoice
                                                </a>
                                                <a href="#" class="text-rose-600 text-sm font-medium hover:underline">
                                                    Contact provider for details →
                                                </a>
                                            </div>
                                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                                            <div class="bg-red-50 text-red-800 px-4 py-2 rounded-lg font-medium text-sm mb-3">
                                                Cancelled
                                            </div>
                                            <a href="<?= BASE_URL ?>customer/dashboard.php#services" class="text-rose-600 text-sm font-medium hover:underline">
                                                Find alternative services →
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBookingDrawer() {
            const drawer = document.getElementById('bookingDrawer');
            const drawerContent = document.getElementById('drawerContent');
            drawer.classList.remove('hidden');
            setTimeout(() => {
                drawerContent.classList.remove('translate-x-full');
            }, 10);
        }
        
        function closeBookingDrawer() {
            const drawer = document.getElementById('bookingDrawer');
            const drawerContent = document.getElementById('drawerContent');
            drawerContent.classList.add('translate-x-full');
            setTimeout(() => {
                drawer.classList.add('hidden');
            }, 300);
        }
    </script>

        <!-- Booking Tips -->
        <div class="mt-12 bg-blue-50 rounded-2xl border border-blue-200 p-6">
            <h3 class="font-bold text-lg text-blue-800 mb-3">💡 Booking Tips for Nadiad Weddings</h3>
            <ul class="space-y-2 text-stone-700 text-sm">
                <li class="flex items-start">
                    <span class="text-blue-500 mr-2 mt-1">✓</span>
                    <span><strong>Pending bookings:</strong> Providers have 12 hours to accept your request. Check back soon!</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-500 mr-2 mt-1">✓</span>
                    <span><strong>Confirmed bookings:</strong> Contact the provider directly to discuss wedding day details.</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-500 mr-2 mt-1">✓</span>
                    <span><strong>Cancelled bookings:</strong> No charges apply. You can book alternative services immediately.</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-500 mr-2 mt-1">✓</span>
                    <span>Book services at least <strong>3-6 months</strong> before your wedding date for best availability in Nadiad.</span>
                </li>
            </ul>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>


</body>
</html>