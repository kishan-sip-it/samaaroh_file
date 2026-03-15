<?php
// PROFESSIONAL BUSINESS FOOTER - Appears on all pages
// Usage: include 'includes/footer.php'; before closing </body> tag

require_once __DIR__ . '/../config/config.php';
?>
<footer class="bg-gradient-to-br from-stone-900 via-stone-800 to-stone-900 text-stone-300 py-8 mt-12 border-t border-stone-700 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 lg:gap-8">
            
            <!-- Brand Section -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <!-- SVG Logo (footer) -->
                    <svg width="48" height="48" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="bg-gradient-to-br from-rose-600 to-amber-500 rounded-xl p-2 shadow-lg">
                        <circle cx="20" cy="20" r="16" stroke="#ffffff" stroke-width="2" fill="none"/>
                        <circle cx="20" cy="20" r="12" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
                        <path d="M20 8 L24 16 L20 24 L16 16 Z" fill="#ffffff"/>
                        <path d="M20 8 L22 14 L20 16 L18 14 Z" fill="#fbbf24"/>
                    </svg>
                    <div>
                        <h2 class="heading text-3xl font-bold text-white">SAMAAROH</h2>
                        <p class="text-rose-400 text-sm font-medium">Wedding Planning Excellence</p>
                    </div>
                </div>
                
                <p class="text-stone-400 mb-4 leading-relaxed">
                    Nadiad's premier wedding planning platform connecting families with verified vendors since 2024. 
                    From traditional Dbagiwala to luxury venues - we make your dream wedding a reality.
                </p>
                
                <!-- Professional Stats -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-rose-400">500+</div>
                        <div class="text-xs text-stone-500">Happy Couples</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-amber-400">50+</div>
                        <div class="text-xs text-stone-500">Verified Vendors</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400">100%</div>
                        <div class="text-xs text-stone-500">Satisfaction</div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="flex items-center gap-4">
                    <span class="text-stone-500 text-sm font-medium">Follow Us:</span>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/" class="w-10 h-10 bg-stone-800 hover:bg-rose-600 rounded-lg flex items-center justify-center text-stone-400 hover:text-white transition-all duration-300 group">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/" class="w-10 h-10 bg-stone-800 hover:bg-rose-600 rounded-lg flex items-center justify-center text-stone-400 hover:text-white transition-all duration-300 group">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/919876543210" class="w-10 h-10 bg-stone-800 hover:bg-green-600 rounded-lg flex items-center justify-center text-stone-400 hover:text-white transition-all duration-300 group">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-stone-800 hover:bg-blue-600 rounded-lg flex items-center justify-center text-stone-400 hover:text-white transition-all duration-300 group">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Services -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4 flex items-center">
                    <span class="w-8 h-8 bg-rose-600 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-sm">🎪</span>
                    </span>
                    Our Services
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?= BASE_URL ?>#services" class="text-stone-400 hover:text-rose-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Dbagiwala Services
                    </a></li>
                    <li><a href="<?= BASE_URL ?>#services" class="text-stone-400 hover:text-rose-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Party Plot Booking
                    </a></li>
                    <li><a href="<?= BASE_URL ?>#services" class="text-stone-400 hover:text-rose-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Wedding Catering
                    </a></li>
                    <li><a href="<?= BASE_URL ?>#services" class="text-stone-400 hover:text-rose-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Photography & Video
                    </a></li>
                    <li><a href="<?= BASE_URL ?>#packages" class="text-stone-400 hover:text-rose-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Wedding Packages
                    </a></li>
                </ul>
            </div>
            
            <!-- For Customers -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4 flex items-center">
                    <span class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-sm">👰</span>
                    </span>
                    For Couples
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?= BASE_URL ?>register.php" class="text-stone-400 hover:text-amber-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Create Account
                    </a></li>
                    <li><a href="<?= BASE_URL ?>login.php" class="text-stone-400 hover:text-amber-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Sign In
                    </a></li>
                    <li><a href="<?= BASE_URL ?>customer/dashboard.php" class="text-stone-400 hover:text-amber-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        My Dashboard
                    </a></li>
                    <li><a href="<?= BASE_URL ?>customer/my_bookings.php" class="text-stone-400 hover:text-amber-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        My Bookings
                    </a></li>
                    <li><a href="<?= BASE_URL ?>report.php" class="text-stone-400 hover:text-amber-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Report Issue
                    </a></li>
                </ul>
            </div>
            
            <!-- For Providers -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4 flex items-center">
                    <span class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-sm">🤝</span>
                    </span>
                    For Providers
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?= BASE_URL ?>register.php?role=provider" class="text-stone-400 hover:text-green-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        List Your Service
                    </a></li>
                    <li><a href="<?= BASE_URL ?>provider/dashboard.php" class="text-stone-400 hover:text-green-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Provider Dashboard
                    </a></li>
                    <li><a href="<?= BASE_URL ?>provider/profile.php" class="text-stone-400 hover:text-green-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        My Profile
                    </a></li>
                    <li><a href="<?= BASE_URL ?>contact.php" class="text-stone-400 hover:text-green-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Support Center
                    </a></li>
                    <li><a href="<?= BASE_URL ?>feedback.php" class="text-stone-400 hover:text-green-400 transition-colors duration-300 flex items-center group">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        Send Feedback
                    </a></li>
                </ul>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-stone-700 my-6"></div>
        
        <!-- Bottom Section -->
        <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
            <!-- Copyright -->
            <div class="text-center lg:text-left">
                <p class="text-stone-400 text-sm mb-2">
                    &copy; 2024 Samaaroh Wedding Planning Platform. All rights reserved.
                </p>
                <p class="text-stone-500 text-xs">
                    BCA Final Year Project • Kishan Marwadi, Shainy Jadav, Kush Patel • Made with ❤️ in Nadiad
                </p>
            </div>
            
            <!-- Legal Links -->
            <div class="flex flex-wrap justify-center lg:justify-end gap-6 text-sm">
                <a href="<?= BASE_URL ?>terms.php" class="text-stone-400 hover:text-white transition-colors duration-300 font-medium">Terms of Service</a>
                <a href="<?= BASE_URL ?>privacy.php" class="text-stone-400 hover:text-white transition-colors duration-300 font-medium">Privacy Policy</a>
                <a href="<?= BASE_URL ?>about.php" class="text-stone-400 hover:text-white transition-colors duration-300 font-medium">About Us</a>
                <a href="<?= BASE_URL ?>contact.php" class="text-stone-400 hover:text-white transition-colors duration-300 font-medium">Contact</a>
            </div>
        </div>
        
        <!-- Trust Badges -->
        <div class="mt-6 pt-6 border-t border-stone-700">
            <div class="flex flex-wrap justify-center items-center gap-8 text-xs text-stone-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Verified Vendors</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>Premium Quality</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>24/7 Support</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                    <span>Trusted by 500+ Couples</span>
                </div>
            </div>
        </div>
    </div>
</footer>