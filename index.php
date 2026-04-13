<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samaaroh | Premium Wedding Planning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/hero.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900">

    <!-- Professional Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section with Animation -->
    <header class="hero-slider relative overflow-hidden h-screen">
        <!-- Background Images Container -->
        <div class="hero-backgrounds">
            <div class="hero-bg active" style="background-image: url('<?= BASE_URL ?>images/banner.jpg')"></div>
            <div class="hero-bg" style="background-image: url('<?= BASE_URL ?>images/image2.jpg')"></div>
            <div class="hero-bg" style="background-image: url('<?= BASE_URL ?>images/image3.jpg')"></div>
            <div class="hero-bg" style="background-image: url('<?= BASE_URL ?>images/image4.jpg')"></div>
            <div class="hero-bg" style="background-image: url('<?= BASE_URL ?>images/image5.jpg')"></div>
        </div>
        
        <!-- Overlay -->
        <div class="hero-overlay"></div>
        
        <!-- Content -->
        <div class="hero-content">
            <div class="max-w-3xl">
                <h1 class="heading text-4xl md:text-6xl font-bold mb-6 text-white drop-shadow-lg">
                    Your Dream Wedding <span class="text-amber-300">Perfectly Planned</span>
                </h1>
                <p class="text-xl text-stone-100 mb-10 max-w-2xl mx-auto drop-shadow-md">
                    Book verified vendors, caterers, photographers & decorators in one place. 
                    Trusted wedding platform since 2026.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?= BASE_URL ?>register.php" class="bg-stone-900 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-800 shadow-xl transition transform hover:scale-105">
                        Plan Your Wedding
                    </a>
                    <a href="<?= BASE_URL ?>register.php?role=provider" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/20 transition">
                        List Your Service
                    </a>
                </div>
                
                <!-- Social Proof -->
                <div class="mt-12 flex flex-wrap justify-center gap-8 text-white/90 text-sm">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">500+</span>
                        <span>Verified Vendors</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">1,200+</span>
                        <span>Happy Weddings</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-amber-300 mr-2">Trusted</span>
                        <span>& Reliable</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Decor Elements -->
        <div class="absolute bottom-16 left-1/2 transform -translate-x-1/2 flex gap-4 animate-bounce z-20">
            <span class="text-4xl">👰</span>
            <span class="text-4xl">🤵</span>
            <span class="text-4xl">🎉</span>
        </div>
    </header>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="heading text-3xl md:text-4xl mb-4 text-stone-800">Trusted by Top Vendors</h2>
            <p class="text-stone-500 max-w-2xl mx-auto mb-16">
                From Das Bagiwala to party plots — verified vendors who understand Every wedding traditions
            </p>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="p-6 bg-stone-50 rounded-2xl hover:shadow-xl transition transform hover:scale-105">
                    <div class="text-5xl mb-4">🐎</div>
                    <h3 class="font-bold text-lg mb-2">Das Bagiwala</h3>
                    <p class="text-stone-600 text-sm">Traditional baggi services with decorated Horses</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl hover:shadow-xl transition transform hover:scale-105">
                    <div class="text-5xl mb-4">🎪</div>
                    <h3 class="font-bold text-lg mb-2">Party Plots</h3>
                    <p class="text-stone-600 text-sm">Various venues and locations</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl hover:shadow-xl transition transform hover:scale-105">
                    <div class="text-5xl mb-4">🍲</div>
                    <h3 class="font-bold text-lg mb-2">Catering</h3>
                    <p class="text-stone-600 text-sm">Authentic thali & multi-cuisine options</p>
                </div>
                <div class="p-6 bg-stone-50 rounded-2xl hover:shadow-xl transition transform hover:scale-105">
                    <div class="text-5xl mb-4">📸</div>
                    <h3 class="font-bold text-lg mb-2">Photography</h3>
                    <p class="text-stone-600 text-sm">Pre-wedding shoots to wedding day coverage</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-3xl md:text-4xl text-center mb-4 text-stone-800">How Samaaroh Works</h2>
            <p class="text-stone-500 text-center max-w-2xl mx-auto mb-16">
                The stress-free way to plan your wedding — from Baggi's to party plot
            </p>
            
            <div class="grid md:grid-cols-3 gap-10">
                <!-- Step 1 -->
                <div class="text-center p-8 bg-white rounded-3xl border border-stone-100 hover:shadow-xl transition transform hover:scale-105">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-700 font-bold text-xl">1</div>
                    <h3 class="heading text-xl font-bold mb-3">Choose Services</h3>
                    <p class="text-stone-600">
                        Select individual services ( baggi, catering, photography) 
                        OR pick a pre-built package (₹10L/15L/30L)
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center p-8 bg-white rounded-3xl border border-stone-100 hover:shadow-xl transition transform hover:scale-105">
                    <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-700 font-bold text-xl">2</div>
                    <h3 class="heading text-xl font-bold mb-3">Request & Wait</h3>
                    <p class="text-stone-600">
                        Provider gets notified instantly. They have <strong>12 hours</strong> to accept your request 
                        (like Uber for wedding vendors)
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center p-8 bg-white rounded-3xl border border-stone-100 hover:shadow-xl transition transform hover:scale-105">
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
    <section id="packages" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-3xl md:text-4xl text-center mb-4 text-stone-800">Wedding Packages</h2>
            <p class="text-stone-500 text-center max-w-2xl mx-auto mb-16">
                Pre-built packages curated for weddings — including party plots, catering, decor & more
            </p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Standard Package -->
                <div class="bg-white rounded-3xl overflow-hidden border border-stone-200 hover:shadow-2xl transition duration-500 transform hover:scale-105">
                    <div class="p-8">
                        <span class="text-rose-600 font-bold uppercase tracking-widest text-sm">Standard</span>
                        <h3 class="heading text-3xl font-bold mt-2 text-stone-800">₹10 Lakhs</h3>
                        <p class="mt-4 text-stone-600">Perfect for intimate 200-guest weddings</p>
                        
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
                                <span>Das Bagiwala Baggi (2 hours)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-rose-500 mr-2 mt-1">✓</span>
                                <span>Photography (4 hours)</span>
                            </li>
                        </ul>
                        
                        <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-stone-900 text-white py-3 rounded-xl text-center font-semibold hover:bg-stone-800 transition">
                            Book This Package
                        </a>
                    </div>
                </div>
                
                <!-- Premium Package (Featured) -->
                <div class="bg-white rounded-3xl overflow-hidden border-2 border-rose-200 relative group hover:shadow-2xl transition duration-500 transform hover:scale-105">
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
                                <span>Premium Party Plot (Central Location)</span>
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
                        
                        <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-rose-600 text-white py-3 rounded-xl text-center font-semibold hover:bg-rose-700 transition shadow-lg">
                            Book This Package
                        </a>
                    </div>
                </div>
                
                <!-- Luxury Package -->
                <div class="bg-stone-900 text-white rounded-3xl overflow-hidden border border-stone-800 hover:shadow-2xl transition duration-500 transform hover:scale-105">
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
                        
                        <a href="<?= BASE_URL ?>register.php" class="mt-10 block w-full bg-amber-400 text-stone-900 py-3 rounded-xl text-center font-semibold hover:bg-amber-300 transition">
                            Book This Package
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Hero Slider JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Hero Slider Functionality
        const heroBackgrounds = document.querySelectorAll('.hero-bg');
        let currentSlide = 0;
        
        function nextSlide() {
            // Remove active class from current slide
            heroBackgrounds[currentSlide].classList.remove('active');
            
            // Move to next slide
            currentSlide = (currentSlide + 1) % heroBackgrounds.length;
            
            // Add active class to next slide
            heroBackgrounds[currentSlide].classList.add('active');
        }
        
        // Auto-advance slides every 4 seconds
        setInterval(nextSlide, 4000);
        
        // Image Path Validation
        if (window.location.hostname === 'localhost') {
            const testImg = new Image();
            testImg.src = '<?= BASE_URL ?>images/banner.jpg';
            testImg.onerror = () => {
                alert('⚠️ IMAGE SETUP REQUIRED:\n\nCreate folder: C:\\wamp64\\www\\samaaroh_file\\images\\\n\nAdd these 5 images:\n- banner.jpg\n- image2.jpg\n- image3.jpg\n- image4.jpg\n- image5.jpg\n\n(Download from Unsplash: search "wedding")');
            };
        }
    });
    </script>
</body>
</html>
