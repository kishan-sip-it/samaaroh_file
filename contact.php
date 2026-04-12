<?php require_once 'config/config.php'; ?>

<?php
// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $service_type = $_POST['service_type'] ?? '';
    
    if (empty($name) || empty($email) || empty($message)) {
        setAlert("Please fill in all required fields.", "error");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setAlert("Please enter a valid email address.", "error");
    } else {
        // Here you would normally send an email or save to database
        // For demo, we'll just show success message
        setAlert("Thank you for contacting us! We'll get back to you within 24 hours.", "success");
        
        // Clear form data
        $name = $email = $phone = $message = $service_type = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Samaaroh</title>
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
    <?php displayAlert(); ?>

    <div class="text-center mb-16">
        <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Contact Us</h1>
        <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
            Have questions about your wedding planning? Need help with booking? We're here to help make your dream wedding a reality.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
            <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Send us a Message</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Your Name *</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                            placeholder="John Doe" value="<?= htmlspecialchars($name ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Email Address *</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                            placeholder="john@example.com" value="<?= htmlspecialchars($email ?? '') ?>">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="+91 98765 43210" value="<?= htmlspecialchars($phone ?? '') ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Service Type</label>
                    <select name="service_type"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                        <option value="">Select a service</option>
                        <option value="bagiwala" <?= ($service_type ?? '') === 'bagiwala' ? 'selected' : '' ?>>Bagiwala</option>
                        <option value="party-plot" <?= ($service_type ?? '') === 'party-plot' ? 'selected' : '' ?>>Party Plot</option>
                        <option value="catering" <?= ($service_type ?? '') === 'catering' ? 'selected' : '' ?>>Catering</option>
                        <option value="photography" <?= ($service_type ?? '') === 'photography' ? 'selected' : '' ?>>Photography</option>
                        <option value="decoration" <?= ($service_type ?? '') === 'decoration' ? 'selected' : '' ?>>Decoration</option>
                        <option value="full-package" <?= ($service_type ?? '') === 'full-package' ? 'selected' : '' ?>>Full Wedding Package</option>
                        <option value="other" <?= ($service_type ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Message *</label>
                    <textarea name="message" required rows="5"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition"
                        placeholder="Tell us about your wedding plans..."><?= htmlspecialchars($message ?? '') ?></textarea>
                </div>
                
                <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-semibold text-lg transition transform hover:scale-105">
                    Send Message
                </button>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="space-y-8">
            <div class="bg-gradient-to-br from-rose-50 to-amber-50 rounded-2xl p-8">
                <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Get in Touch</h2>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-rose-600 text-xl">📍</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-800 mb-1">Office Address</h3>
                            <p class="text-stone-600">
                                Sangath Road, Near Mahudi Circle<br>
                                Nadiad, Gujarat 387002<br>
                                India
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-amber-600 text-xl">📞</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-800 mb-1">Phone Support</h3>
                            <p class="text-stone-600">
                                +91 98765 43210<br>
                                Mon-Sat: 9:00 AM - 7:00 PM<br>
                                Sunday: 10:00 AM - 2:00 PM
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-green-600 text-xl">✉️</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-800 mb-1">Email Support</h3>
                            <p class="text-stone-600">
                                info@samaaroh.com<br>
                                support@samaaroh.com<br>
                                24/7 Online Support
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-stone-900 rounded-2xl p-8 text-white text-center">
                <div class="text-6xl mb-4">⚡</div>
                <h3 class="heading text-xl font-bold mb-2">Quick Response</h3>
                <p class="text-stone-300 mb-4">
                    We respond to all inquiries within 24 hours. For urgent wedding planning needs, call us directly!
                </p>
                <a href="tel:+919876543210" 
                    class="inline-block bg-amber-500 hover:bg-amber-400 text-stone-900 px-6 py-3 rounded-xl font-semibold transition">
                    Call Now
                </a>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-20">
        <h2 class="heading text-3xl font-bold text-center text-stone-800 mb-12">Frequently Asked Questions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                <h3 class="font-bold text-lg text-stone-800 mb-3">How quickly can I book vendors?</h3>
                <p class="text-stone-600">
                    Once you submit a booking request, vendors have 12 hours to accept. Most bookings are confirmed within 24 hours.
                </p>
            </div>
            
            <div class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                <h3 class="font-bold text-lg text-stone-800 mb-3">Are all vendors verified?</h3>
                <p class="text-stone-600">
                    Yes! Every vendor on Samaaroh undergoes strict verification including background checks and service quality reviews.
                </p>
            </div>
            
            <div class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                <h3 class="font-bold text-lg text-stone-800 mb-3">Can I customize wedding packages?</h3>
                <p class="text-stone-600">
                    Absolutely! Our packages are starting points. You can add, remove, or modify services based on your specific needs.
                </p>
            </div>
            
            <div class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                <h3 class="font-bold text-lg text-stone-800 mb-3">What if a vendor cancels?</h3>
                <p class="text-stone-600">
                    We guarantee replacement vendors at the same price if any confirmed vendor cancels. Your wedding is our priority!
                </p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
