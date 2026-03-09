<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access your dashboard", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// FETCH SERVICES (grouped by tier)
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name 
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE s.is_available = 1 
    ORDER BY 
        CASE s.tier 
            WHEN 'standard' THEN 1 
            WHEN 'premium' THEN 2 
            WHEN 'luxury' THEN 3 
        END,
        s.category,
        s.price ASC
");
$stmt->execute();
$services = $stmt->fetchAll();

// FETCH USER'S BOOKINGS
$bookings_stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, s.price as service_price 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.id 
    WHERE b.customer_id = ? 
    ORDER BY b.booking_date DESC
");
$bookings_stmt->execute([$_SESSION['user_id']]);
$bookings = $bookings_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .service-card { transition: transform 0.3s, box-shadow 0.3s; }
        .service-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .tier-badge { font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; }
        .tier-standard { background-color: #dbeafe; color: #1e40af; }
        .tier-premium { background-color: #fef3c7; color: #92400e; }
        .tier-luxury { background-color: #fbcfe8; color: #9d174d; }
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
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">Plan Your Perfect Wedding</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Browse verified vendors from Nadiad —  Bagiwalas, party plots, caterers & more
            </p>
        </div>

        <!-- Bookings Summary -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-stone-800">My Bookings</h2>
                <a href="#services" class="text-rose-600 font-medium text-sm hover:underline">Browse Services →</a>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                    <div class="text-stone-300 text-5xl mb-4">📭</div>
                    <p class="text-stone-500">You haven't booked any services yet</p>
                    <a href="#services" class="mt-4 inline-block bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-700 transition">
                        Explore Services
                    </a>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="bg-white rounded-2xl border border-stone-200 p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="tier-badge tier-<?= $booking['status'] === 'confirmed' ? 'premium' : 'standard' ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">12h window</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="font-bold text-lg"><?= htmlspecialchars($booking['service_title'] ?? 'Package Booking') ?></h3>
                                    <p class="text-stone-500 text-sm mt-1">
                                        <?= ucfirst($booking['category'] ?? 'N/A') ?> • 
                                        ₹<?= number_format($booking['total_price'], 0) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-rose-600">₹<?= number_format($booking['total_price'], 0) ?></div>
                                    <div class="text-xs text-stone-400 mt-1">
                                        Booked: <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($booking['event_date']): ?>
                                <div class="mt-4 pt-4 border-t border-stone-100">
                                    <div class="flex items-center text-sm text-stone-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Wedding Date: <span class="font-medium ml-1"><?= date('M d, Y', strtotime($booking['event_date'])) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Services by Tier -->
        <?php 
        $tiers = ['standard' => 'Standard Celebration', 'premium' => 'Premium Experience', 'luxury' => 'Luxury Affair'];
        foreach ($tiers as $tier_key => $tier_name): 
            // PHP 7.0+ COMPATIBLE FILTER (NO ARROW FUNCTIONS)
            $tier_services = array_filter($services, function($s) use ($tier_key) {
                return $s['tier'] === $tier_key;
            });
            if (empty($tier_services)) continue;
        ?>
            <section class="mb-16" id="<?= $tier_key ?>-services">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800">
                        <?= $tier_name ?> 
                        <span class="text-rose-600 text-lg">
                            <?php 
                            $price_ranges = [
                                'standard' => '₹5-15L', 
                                'premium' => '₹15-30L', 
                                'luxury' => '₹30L+'
                            ];
                            echo $price_ranges[$tier_key];
                            ?>
                        </span>
                    </h2>
                    <a href="#how-it-works" class="text-rose-600 text-sm font-medium hover:underline hidden md:block">
                        How booking works →
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($tier_services as $service): ?>
                        <div class="service-card bg-white rounded-2xl overflow-hidden border border-stone-200">
                            <div class="h-48 bg-stone-100 relative">
                                <?php if (!empty($service['image_path'])): ?>
                                    <img src="<?= UPLOADS_URL ?><?= htmlspecialchars($service['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($service['title']) ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-50 to-amber-50">
                                        <span class="text-4xl">
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
                                    <span class="tier-badge tier-<?= $service['tier'] ?>">
                                        <?= ucfirst($service['tier']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($service['title']) ?></h3>
                                    <span class="font-bold text-rose-600">₹<?= number_format($service['price'], 0) ?></span>
                                </div>
                                
                                <p class="text-stone-500 text-sm mb-4 line-clamp-2">
                                    <?= htmlspecialchars(substr($service['description'], 0, 100)) ?>...
                                </p>
                                
                                <div class="flex items-center justify-between mb-5">
                                    <div class="flex items-center">
                                        <span class="text-amber-400">★★★★★</span>
                                        <span class="text-stone-400 text-sm ml-1">Verified</span>
                                    </div>
                                    <span class="text-xs bg-stone-100 text-stone-700 px-2 py-1 rounded-full">
                                        <?= ucfirst($service['category']) ?>
                                    </span>
                                </div>
                                
                                <form method="POST" action="<?= BASE_URL ?>customer/book_service.php" class="space-y-3">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <input type="hidden" name="price" value="<?= $service['price'] ?>">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-stone-700 mb-1">Wedding Date</label>
                                        <input type="date" name="event_date" required min="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                    </div>
                                    
                                    <button type="submit" 
                                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-3 rounded-xl transition flex items-center justify-center gap-2">
                                        <span>Book This Service</span>
                                        <span>→</span>
                                    </button>
                                    
                                    <p class="text-xs text-stone-400 text-center mt-2">
                                        Provider has 12 hours to accept your request
                                    </p>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- How It Works -->
        <section id="how-it-works" class="py-16 bg-stone-50 rounded-3xl mt-12">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="heading text-3xl font-bold text-stone-800 mb-4">How Booking Works</h2>
                <p class="text-stone-500 mb-10 max-w-2xl mx-auto">
                    Stress-free planning for your Gujarati wedding in Nadiad
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-6 bg-white rounded-2xl border border-stone-200">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-700 font-bold text-xl">1</div>
                        <h3 class="font-bold text-lg mb-2">Select & Book</h3>
                        <p class="text-stone-600">Choose services like Das Bagiwala or party plot. Select your wedding date and submit request.</p>
                    </div>
                    
                    <div class="p-6 bg-white rounded-2xl border border-stone-200">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-700 font-bold text-xl">2</div>
                        <h3 class="font-bold text-lg mb-2">12-Hour Window</h3>
                        <p class="text-stone-600">Provider gets notified instantly. They have 12 hours to accept your request (like Uber for weddings).</p>
                    </div>
                    
                    <div class="p-6 bg-white rounded-2xl border border-stone-200">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-700 font-bold text-xl">3</div>
                        <h3 class="font-bold text-lg mb-2">Pay & Celebrate</h3>
                        <p class="text-stone-600">Pay securely after confirmation. Focus on your celebration — we handle vendor coordination.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    </script>
</body>
</html>