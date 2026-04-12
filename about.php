<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-16">
        <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">About Samaaroh</h1>
        <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
            Nadiad's trusted wedding planning platform connecting families with verified vendors since 2026
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
        <div>
            <h2 class="heading text-3xl font-bold text-stone-800 mb-6">Our Story</h2>
            <p class="text-stone-600 mb-4">
                Samaaroh was born from a simple observation: wedding planning in Nadiad is fragmented. Families spend months contacting bagiwalas, party plots, caterers, and photographers separately - often with inconsistent quality and last-minute cancellations.
            </p>
            <p class="text-stone-600 mb-4">
                Founded in 2026 by Kishan Marwadi (BCA student), Samaaroh solves this by creating a unified platform where verified vendors showcase their services and families book everything in one place - with transparent pricing and reliable 12-hour acceptance workflow.
            </p>
            <p class="text-stone-600">
                Today, we proudly serve hundreds of Nadiad families and partner with 50+ verified vendors across Sangath, Mahudi Road, and central Nadiad areas.
            </p>
        </div>
        <div class="bg-gradient-to-br from-rose-50 to-amber-50 rounded-2xl p-8 text-center">
            <div class="text-8xl mb-4">✨</div>
            <h3 class="heading text-2xl font-bold text-stone-800 mb-2">"One Platform, Perfect Wedding"</h3>
            <p class="text-stone-600">
                Our mission: Make Gujarati wedding planning stress-free, transparent, and joyful for every family in Nadiad
            </p>
        </div>
    </div>

    <div class="mb-20">
        <h2 class="heading text-3xl font-bold text-center text-stone-800 mb-12">Why Families Trust Us</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-600 text-2xl">
                    ✓
                </div>
                <h3 class="font-bold text-xl text-stone-800 mb-3">Verified Vendors Only</h3>
                <p class="text-stone-600">
                    Every bagiwala, party plot, and caterer undergoes strict verification. No fake profiles, no last-minute cancellations.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600 text-2xl">
                    ⏱️
                </div>
                <h3 class="font-bold text-xl text-stone-800 mb-3">12-Hour Acceptance Guarantee</h3>
                <p class="text-stone-600">
                    Providers must accept/reject bookings within 12 hours. No endless waiting like traditional planning.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600 text-2xl">
                    💰
                </div>
                <h3 class="font-bold text-xl text-stone-800 mb-3">Transparent Pricing</h3>
                <p class="text-stone-600">
                    No hidden costs. See exact pricing for bagiwala chariot, party plots, catering before booking.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-20">
        <div class="bg-stone-900 rounded-2xl p-8 text-white">
            <h2 class="heading text-2xl font-bold mb-4">Our Impact</h2>
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="text-center">
                    <p class="text-4xl font-bold text-amber-300 mb-2">250+</p>
                    <p class="text-stone-300">Verified Vendors</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-amber-300 mb-2">1,200+</p>
                    <p class="text-stone-300">Happy Weddings</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-amber-300 mb-2">50+</p>
                    <p class="text-stone-300">Service Categories</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-amber-300 mb-2">98%</p>
                    <p class="text-stone-300">Success Rate</p>
                </div>
            </div>
            <p class="text-stone-300">
                We're proud to have transformed wedding planning for hundreds of families in Nadiad, making it a joyful experience rather than a stressful chore.
            </p>
        </div>
        
        <div class="bg-white rounded-2xl border border-stone-200 p-8">
            <h2 class="heading text-2xl font-bold mb-4">Our Team</h2>
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center">
                        <span class="text-rose-600 font-bold">KM</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-stone-800">Kishan Marwadi</h3>
                        <p class="text-stone-600 text-sm">Founder & CEO</p>
                        <p class="text-stone-500 text-xs">BCA Final Year Student</p>
                    </div>
                </div>
                <p class="text-stone-600">
                    Led by a passionate team of wedding enthusiasts and tech professionals, we're committed to revolutionizing wedding planning in Gujarat.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-stone-900 rounded-2xl p-12 text-center text-white">
        <h2 class="heading text-3xl font-bold mb-4">Join Nadiad's Wedding Revolution</h2>
        <p class="text-stone-300 max-w-2xl mx-auto mb-8">
            Whether you're planning your dream wedding or offering wedding services in Nadiad, Samaaroh connects you with the right people at the right time.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= BASE_URL ?>register.php" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                Plan Your Wedding
            </a>
            <a href="<?= BASE_URL ?>register.php?role=provider" class="bg-white hover:bg-stone-100 text-stone-900 px-8 py-4 rounded-xl font-bold text-lg transition">
                List Your Service
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
