<?php
// DYNAMIC NAVIGATION BAR - Adapts to user role and login status
// Usage: include 'includes/navbar.php'; in page body

require_once __DIR__ . '/../config/config.php';
?>
<nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-50 border-b border-stone-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-2">
                <!-- Logo Image (uncomment if you have logo.png) -->
                <!-- <img src="<?= BASE_URL ?>assets/logo.png" alt="Samaaroh" class="h-10 w-10"> -->
                
                <!-- SVG Logo (current) -->
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="16" stroke="#e11d48" stroke-width="2" fill="none"/>
                    <circle cx="20" cy="20" r="12" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
                    <path d="M20 8 L24 16 L20 24 L16 16 Z" fill="#e11d48"/>
                    <path d="M20 8 L22 14 L20 16 L18 14 Z" fill="#fbbf24"/>
                </svg>
                
                <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700 hover:text-rose-800 transition">
                    SAMAAROH
                </a>
            </div>
            
            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-8">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'customer'): ?>
                        <a href="<?= BASE_URL ?>customer/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Dashboard</a>
                        <a href="<?= BASE_URL ?>customer/my_bookings.php" class="text-stone-600 hover:text-rose-600 font-medium transition">My Bookings</a>
                        <a href="<?= BASE_URL ?>customer/profile.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Profile</a>
                    <?php elseif ($_SESSION['role'] === 'provider'): ?>
                        <a href="<?= BASE_URL ?>provider/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Dashboard</a>
                        <a href="<?= BASE_URL ?>provider/profile.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Profile</a>
                    <?php else: // admin ?>
                        <a href="<?= BASE_URL ?>admin/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Admin Panel</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>#services" class="text-stone-600 hover:text-rose-600 font-medium transition">Services</a>
                    <a href="<?= BASE_URL ?>#packages" class="text-stone-600 hover:text-rose-600 font-medium transition">Packages</a>
                    <a href="<?= BASE_URL ?>#how-it-works" class="text-stone-600 hover:text-rose-600 font-medium transition">How It Works</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>report.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Report Issue</a>
                <a href="<?= BASE_URL ?>feedback.php" class="text-stone-600 hover:text-rose-600 font-medium transition">Feedback</a>
            </div>
            
            <!-- Auth Buttons -->
            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="hidden md:block text-right">
                        <div class="text-sm font-medium text-stone-800"><?= htmlspecialchars($_SESSION['name']) ?></div>
                        <div class="text-xs text-rose-600 capitalize"><?= htmlspecialchars($_SESSION['role']) ?></div>
                    </div>
                    <a href="<?= BASE_URL ?>logout.php" 
                       class="bg-stone-100 hover:bg-stone-200 text-stone-700 px-4 py-2 rounded-lg font-medium text-sm transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm transition">Login</a>
                    <a href="<?= BASE_URL ?>register.php" 
                       class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition">
                        Get Started
                    </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Button -->
                <button class="md:hidden text-stone-600 hover:text-rose-600 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu (Hidden by default) -->
<div class="md:hidden fixed inset-0 bg-white z-40 p-6 transform translate-x-full transition-transform duration-300" id="mobile-menu">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-2">
            <!-- SVG Logo (mobile) -->
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="16" stroke="#e11d48" stroke-width="2" fill="none"/>
                <circle cx="20" cy="20" r="12" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
                <path d="M20 8 L24 16 L20 24 L16 16 Z" fill="#e11d48"/>
                <path d="M20 8 L22 14 L20 16 L18 14 Z" fill="#fbbf24"/>
            </svg>
            <h1 class="heading text-2xl font-bold text-rose-700">SAMAAROH</h1>
        </div>
        <button id="close-menu" class="text-stone-600 hover:text-rose-600 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <div class="flex flex-col space-y-2">
        <a href="<?= BASE_URL ?>report.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Report Issue</a>
        <a href="<?= BASE_URL ?>feedback.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Feedback</a>
    </div>
    
    <div class="space-y-6">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'customer'): ?>
                <a href="<?= BASE_URL ?>customer/dashboard.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Dashboard</a>
                <a href="<?= BASE_URL ?>customer/my_bookings.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">My Bookings</a>
                <a href="<?= BASE_URL ?>customer/profile.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Profile</a>
            <?php elseif ($_SESSION['role'] === 'provider'): ?>
                <a href="<?= BASE_URL ?>provider/dashboard.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Dashboard</a>
                <a href="<?= BASE_URL ?>provider/profile.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Profile</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Admin Panel</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>logout.php" class="block mt-4 bg-stone-100 text-stone-700 py-3 rounded-lg text-center font-medium hover:bg-stone-200 transition">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>#services" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Services</a>
            <a href="<?= BASE_URL ?>#packages" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">Packages</a>
            <a href="<?= BASE_URL ?>#how-it-works" class="block py-3 border-b border-stone-100 text-lg font-medium text-stone-800 hover:text-rose-600 transition">How It Works</a>
            <a href="<?= BASE_URL ?>login.php" class="block mt-4 bg-stone-100 text-stone-700 py-3 rounded-lg text-center font-medium hover:bg-stone-200 transition">Login</a>
            <a href="<?= BASE_URL ?>register.php" class="block mt-2 bg-rose-600 text-white py-3 rounded-lg text-center font-medium hover:bg-rose-700 transition">Get Started</a>
        <?php endif; ?>
    </div>
</div>

<!-- Mobile Menu Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('button.md\\:hidden');
    const closeMenu = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 hidden';
    document.body.appendChild(overlay);
    
    menuButton?.addEventListener('click', () => {
        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        overlay.classList.remove('hidden');
    });
    
    closeMenu?.addEventListener('click', () => {
        mobileMenu.classList.add('translate-x-full');
        mobileMenu.classList.remove('translate-x-0');
        overlay.classList.add('hidden');
    });
    
    overlay.addEventListener('click', () => {
        mobileMenu.classList.add('translate-x-full');
        overlay.classList.add('hidden');
    });
    
    // Close menu when clicking links
    document.querySelectorAll('#mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('translate-x-full');
            overlay.classList.add('hidden');
        });
    });
});
</script>