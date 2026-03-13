<?php 
include 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samaaroh | Premium Wedding Planning</title>
    <!-- Tailwind CSS (FIXED: removed spaces) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom Hero Animation (CORRECT PATH FOR samaaroh_file) -->
    <link rel="stylesheet" href="/samaaroh_file/assets/hero.css">
    <!-- Google Fonts (FIXED: removed spaces) -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
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
<body class="bg-stone-50 text-stone-900">

    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section with Animation (CORRECT PATH) -->
    <header class="hero-slider relative overflow-hidden" style="background-image: url('/samaaroh_file/images/banner.jpg')">
        <div class="hero-overlay"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col items-center justify-center h-full text-center">
            <div class="max-w-3xl">
                <h1 class="heading text-4xl md:text-6xl font-bold mb-6 text-white drop-shadow-lg">
                    Your Dream Gujarati Wedding, <span class="text-amber-300">Perfectly Planned</span>
                </h1>
                <p class="text-xl text-stone-100 mb-10 max-w-2xl mx-auto drop-shadow-md">
                    Book verified Bagiwalas, caterers, photographers & decorators in one place. 
                    Nadiad's trusted wedding platform since 2024.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'customer'): ?>
                            <a href="<?= BASE_URL ?>customer/dashboard.php" class="bg-stone-900 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-800 shadow-xl transition transform hover:scale-105">
                                Plan Your Wedding
                            </a>
                        <?php elseif ($_SESSION['role'] === 'provider'): ?>
                            <a href="<?= BASE_URL ?>provider/dashboard.php" class="bg-stone-900 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-800 shadow-xl transition transform hover:scale-105">
                                Manage Your Services
                            </a>
                        <?php elseif ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>admin/dashboard.php" class="bg-stone-900 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-800 shadow-xl transition transform hover:scale-105">
                                Admin Dashboard
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['role'] === 'provider'): ?>
                            <a href="<?= BASE_URL ?>provider/new_service.php" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/20 transition">
                                List New Service
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>services.php" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/20 transition">
                                Browse Services
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>register.php" class="bg-stone-900 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-800 shadow-xl transition transform hover:scale-105">
                            Plan Your Wedding
                        </a>
                        <a href="<?= BASE_URL ?>register.php?role=provider" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/20 transition">
                            List Your Service
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Social Proof -->
                <div class="mt-12 flex flex-wrap justify-center gap-8 text-white/90 text-sm">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">250+</span>
                        <span>Verified Vendors</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">1,200+</span>
                        <span>Happy Weddings</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">Nadiad</span>
                        <span>Based & Trusted</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Decor Elements -->
        <div class="absolute bottom-16 left-1/2 transform -translate-x-1/2 flex gap-4 animate-bounce">
            <span class="text-4xl">👰</span>
            <span class="text-4xl">🤵</span>
            <span class="text-4xl">🎉</span>
        </div>
    </header>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-3xl md:text-4xl text-center mb-4 text-stone-800">How Samaaroh Works</h2>
            <p class="text-stone-500 text-center max-w-2xl mx-auto mb-16">
                The stress-free way to plan your Gujarati wedding — from Das Bagiwala to party plot
            </p>
            
            <div class="grid md:grid-cols-3 gap-10">
                <!-- Step 1 -->
                <div class="text-center p-8 bg-stone-50 rounded-3xl border border-stone-100 hover:shadow-xl transition">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-700 font-bold text-xl">1</div>
                    <h3 class="heading text-xl font-bold mb-3">Choose Services</h3>
                    <p class="text-stone-600">
                        Select individual services ( buggy, catering, photography) 
                        OR pick a pre-built package (₹10L/15L/30L)
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center p-8 bg-stone-50 rounded-3xl border border-stone-100 hover:shadow-xl transition">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-700 font-bold text-xl">2</div>
                    <h3 class="heading text-xl font-bold mb-3">Request & Wait</h3>
                    <p class="text-stone-600">
                        Provider gets notified instantly. They have <strong>12 hours</strong> to accept your request 
                        (like Uber for wedding vendors)
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center p-8 bg-stone-50 rounded-3xl border border-stone-100 hover:shadow-xl transition">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-700 font-bold text-xl">3</div>
                    <h3 class="heading text-xl font-bold mb-3">Pay & Celebrate</h3>
                    <p class="text-stone-600">
                        Pay securely after confirmation. Focus on your celebration — we handle vendor coordination
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="py-20 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-3xl md:text-4xl text-center mb-4 text-stone-800">Wedding Packages</h2>
            <p class="text-stone-500 text-center max-w-2xl mx-auto mb-16">
                Pre-built packages curated for Nadiad weddings — including party plots, catering, decor & more
            </p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Standard Package -->
                <div class="bg-white rounded-3xl overflow-hidden border border-stone-200 hover:shadow-2xl transition duration-500">
                    <div class="p-8">
                        <span class="text-rose-600 font-bold uppercase tracking-widest text-sm">Standard</span>
                        <h3 class="heading text-3xl font-bold mt-2 text-stone-800">₹10 Lakhs</h3>
                        <p class="mt-4 text-stone-600">Perfect for intimate 200-guest weddings in Nadiad</p>
                        
                        <ul class="mt-8 space-y-3">
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Party Plot (Sangath area)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Basic Decor (Mandap + Entrance)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Catering (200 guests, 4 courses)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Das Bagiwala Buggy (2 hours)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Photography (4 hours)</span>
                            </li>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
    <a href="<?= BASE_URL ?>customer/book_package.php?package_id=1" class="mt-10 block w-full bg-stone-900 text-white py-3 rounded-xl text-center font-semibold hover:bg-stone-800 transition">
        Book This Package
    </a>
<?php else: ?>
    <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-stone-900 text-white py-3 rounded-xl text-center font-semibold hover:bg-stone-800 transition">
        Book This Package
    </a>
<?php endif; ?>
                    </div>
                </div>
                
                <!-- Premium Package (Featured) -->
                <div class="bg-white rounded-3xl overflow-hidden border-2 border-rose-200 relative group">
                    <div class="absolute top-0 right-0 bg-rose-600 text-white px-4 py-1.5 text-sm rounded-bl-xl font-bold z-10">
                        MOST POPULAR
                    </div>
                    <div class="p-8 pt-16">
                        <span class="text-rose-600 font-bold uppercase tracking-widest text-sm">Premium</span>
                        <h3 class="heading text-3xl font-bold mt-2 text-stone-800">₹15 Lakhs</h3>
                        <p class="mt-4 text-stone-600">Ideal for 300-guest weddings with luxury touches</p>
                        
                        <ul class="mt-8 space-y-3">
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Premium Party Plot (Central Nadiad)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Themed Decor (Full Venue Transformation)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Gourmet Catering (300 guests, live counters)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Das Bagiwala + Ghodi (4 hours)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Cinematic Videography + Drone Shots</span>
                            </li>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
    <a href="<?= BASE_URL ?>customer/book_package.php?package_id=2" class="mt-10 block w-full bg-rose-600 text-white py-3 rounded-xl text-center font-semibold hover:bg-rose-700 transition shadow-lg">
        Book This Package
    </a>
<?php else: ?>
    <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-rose-600 text-white py-3 rounded-xl text-center font-semibold hover:bg-rose-700 transition shadow-lg">
        Book This Package
    </a>
<?php endif; ?>
                    </div>
                </div>
                
                <!-- Luxury Package -->
                <div class="bg-stone-900 text-white rounded-3xl overflow-hidden border border-stone-800 hover:shadow-2xl transition duration-500">
                    <div class="p-8">
                        <span class="text-rose-400 font-bold uppercase tracking-widest text-sm">Luxury</span>
                        <h3 class="heading text-3xl font-bold mt-2">₹30+ Lakhs</h3>
                        <p class="mt-4 text-stone-300">Grand destination-style weddings for elite families</p>
                        
                        <ul class="mt-8 space-y-3">
                            <li class="flex items-start">
                                <span class="text-amber-300 mr-2 mt-1">✓</span>
                                <span>Luxury Venue (Resort/Heritage Property)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber-300 mr-2 mt-1">✓</span>
                                <span>Full Venue Transformation (Floral Extravaganza)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber-300 mr-2 mt-1">✓</span>
                                <span>5-Star Catering (500+ guests)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber-300 mr-2 mt-1">✓</span>
                                <span>Das Bagiwala Fleet + Celebrity Entertainment</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-amber-300 mr-2 mt-1">✓</span>
                                <span>Dedicated Wedding Planner + Luxury Transport</span>
                            </li>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
    <a href="<?= BASE_URL ?>customer/book_package.php?package_id=3" class="mt-10 block w-full bg-amber-400 text-stone-900 py-3 rounded-xl text-center font-semibold hover:bg-amber-300 transition">
        Book This Package
    </a>
<?php else: ?>
    <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-amber-400 text-stone-900 py-3 rounded-xl text-center font-semibold hover:bg-amber-300 transition">
        Book This Package
    </a>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="heading text-3xl md:text-4xl mb-4 text-stone-800">Trusted by Nadiad's Top Vendors</h2>
            <p class="text-stone-500 max-w-2xl mx-auto mb-16">
                From Das Bagiwala to party plots — verified vendors who understand Gujarati wedding traditions
            </p>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="p-6 bg-stone-50 rounded-2xl">
                    <div class="text-5xl mb-4">🛺</div>
                    <h3 class="font-bold text-lg mb-2">Das Bagiwala</h3>
                    <p class="text-stone-600 text-sm">Traditional buggy services with decorated Horses</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl">
                    <div class="text-5xl mb-4">🎪</div>
                    <h3 class="font-bold text-lg mb-2">Party Plots</h3>
                    <p class="text-stone-600 text-sm">Sangath, Mahudi Road & central Nadiad venues</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl">
                    <div class="text-5xl mb-4">🍲</div>
                    <h3 class="font-bold text-lg mb-2">Catering</h3>
                    <p class="text-stone-600 text-sm">Authentic Gujarati thali & multi-cuisine options</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl">
                    <div class="text-5xl mb-4">📸</div>
                    <h3 class="font-bold text-lg mb-2">Photography</h3>
                    <p class="text-stone-600 text-sm">Pre-wedding shoots to wedding day coverage</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Image Path Validation (CORRECT PATH FOR samaaroh_file) -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Only show alert in localhost environment
        if (window.location.hostname === 'localhost') {
            const testImg = new Image();
            testImg.src = '/samaaroh_file/images/banner.jpg';
            testImg.onerror = () => {
                alert('⚠️ IMAGE SETUP REQUIRED:\n\nCreate folder: C:\\wamp64\\www\\samaaroh_file\\images\\\n\nAdd these 5 images:\n- banner.jpg\n- image2.jpg\n- image3.jpg\n- image4.jpg\n- image5.jpg\n\n(Download from Unsplash: search "gujarati wedding")');
            };
        }
    });
    </script>
</body>
</html>