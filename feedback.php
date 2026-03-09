<?php
require_once 'config/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $rating = $_POST['rating'];
    $service_type = $_POST['service_type'];
    $feedback_type = $_POST['feedback_type'];
    $comments = trim($_POST['comments']);
    $recommend = $_POST['recommend'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($rating) && !empty($comments)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (name, email, phone, rating, service_type, feedback_type, comments, recommend, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $rating, $service_type, $feedback_type, $comments, $recommend]);
            
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded'>
                          <strong>Thank you!</strong> Your feedback has been submitted successfully.
                        </div>";
        } catch (PDOException $e) {
            error_log("Feedback submission error: " . $e->getMessage());
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                          <strong>Error!</strong> System error. Please try again later.
                        </div>";
        }
    } else {
        $message = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded'>
                      <strong>Warning!</strong> Please fill in all required fields.
                    </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .star-rating { display: flex; gap: 0.5rem; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { cursor: pointer; font-size: 2rem; color: #d1d5db; transition: color 0.2s; }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #fbbf24; }
    </style>
</head>
<body class="bg-stone-50">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-50 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">✨</span>
                    <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700">SAMAAROH</a>
                </div>
                <div class="hidden md:flex space-x-8 font-medium text-stone-600">
                    <a href="<?= BASE_URL ?>index.php" class="hover:text-rose-600 transition">Home</a>
                    <a href="<?= BASE_URL ?>report.php" class="hover:text-rose-600 transition">Report Issue</a>
                    <a href="<?= BASE_URL ?>feedback.php" class="hover:text-rose-600 transition">Feedback</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>login.php" class="text-stone-600 px-4 hover:text-rose-600 font-medium">Login</a>
                    <a href="<?= BASE_URL ?>register.php" class="bg-rose-600 text-white px-6 py-2 rounded-full hover:bg-rose-700 transition font-medium">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-amber-500 to-rose-600 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold text-white mb-6">Share Your Feedback</h1>
            <p class="text-amber-100 text-xl max-w-2xl mx-auto">
                Your feedback helps us improve our services and make every wedding perfect
            </p>
        </div>
    </section>

    <!-- Feedback Form Section -->
    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <?php echo $message; ?>
                
                <h2 class="heading text-2xl font-bold text-stone-800 mb-6">We'd Love to Hear From You</h2>
                
                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="John Doe"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-stone-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="john@example.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-stone-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               placeholder="+91 98765 43210"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="service_type" class="block text-sm font-medium text-stone-700 mb-2">Service Type</label>
                            <select id="service_type" name="service_type"
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <option value="">Select Service</option>
                                <option value="booking" <?= ($_POST['service_type'] ?? '') === 'booking' ? 'selected' : '' ?>>Booking Experience</option>
                                <option value="vendor" <?= ($_POST['service_type'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor Service</option>
                                <option value="package" <?= ($_POST['service_type'] ?? '') === 'package' ? 'selected' : '' ?>>Wedding Package</option>
                                <option value="support" <?= ($_POST['service_type'] ?? '') === 'support' ? 'selected' : '' ?>>Customer Support</option>
                                <option value="other" <?= ($_POST['service_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="feedback_type" class="block text-sm font-medium text-stone-700 mb-2">Feedback Type</label>
                            <select id="feedback_type" name="feedback_type"
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <option value="">Select Type</option>
                                <option value="compliment" <?= ($_POST['feedback_type'] ?? '') === 'compliment' ? 'selected' : '' ?>>Compliment</option>
                                <option value="suggestion" <?= ($_POST['feedback_type'] ?? '') === 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                                <option value="complaint" <?= ($_POST['feedback_type'] ?? '') === 'complaint' ? 'selected' : '' ?>>Complaint</option>
                                <option value="general" <?= ($_POST['feedback_type'] ?? '') === 'general' ? 'selected' : '' ?>>General Feedback</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Overall Rating *</label>
                        <div class="star-rating flex-row-reverse justify-end">
                            <input type="radio" id="star5" name="rating" value="5" required <?= ($_POST['rating'] ?? '') === '5' ? 'checked' : '' ?>>
                            <label for="star5">⭐</label>
                            <input type="radio" id="star4" name="rating" value="4" <?= ($_POST['rating'] ?? '') === '4' ? 'checked' : '' ?>>
                            <label for="star4">⭐</label>
                            <input type="radio" id="star3" name="rating" value="3" <?= ($_POST['rating'] ?? '') === '3' ? 'checked' : '' ?>>
                            <label for="star3">⭐</label>
                            <input type="radio" id="star2" name="rating" value="2" <?= ($_POST['rating'] ?? '') === '2' ? 'checked' : '' ?>>
                            <label for="star2">⭐</label>
                            <input type="radio" id="star1" name="rating" value="1" <?= ($_POST['rating'] ?? '') === '1' ? 'checked' : '' ?>>
                            <label for="star1">⭐</label>
                        </div>
                        <p class="text-xs text-stone-500 mt-1">Click to rate your experience</p>
                    </div>
                    
                    <div>
                        <label for="comments" class="block text-sm font-medium text-stone-700 mb-2">Your Comments *</label>
                        <textarea id="comments" name="comments" rows="6" required
                                  class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                  placeholder="Please share your detailed feedback..."><?= htmlspecialchars($_POST['comments'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Would you recommend Samaaroh to others?</label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="recommend" value="yes" class="mr-2" <?= ($_POST['recommend'] ?? '') === 'yes' ? 'checked' : '' ?>>
                                <span class="text-stone-700">Yes</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="recommend" value="no" class="mr-2" <?= ($_POST['recommend'] ?? '') === 'no' ? 'checked' : '' ?>>
                                <span class="text-stone-700">No</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="recommend" value="maybe" class="mr-2" <?= ($_POST['recommend'] ?? '') === 'maybe' ? 'checked' : '' ?>>
                                <span class="text-stone-700">Maybe</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-4 rounded-xl transition duration-200 shadow-lg hover:shadow-xl">
                        Submit Feedback
                    </button>
                </form>
                
                <div class="mt-8 pt-6 border-t border-stone-200">
                    <h3 class="font-semibold text-stone-800 mb-3">Why your feedback matters</h3>
                    <ul class="space-y-2 text-sm text-stone-600">
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2 mt-1">•</span>
                            <span>Helps us improve our services and vendor partnerships</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2 mt-1">•</span>
                            <span>Allows us to address issues quickly and effectively</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2 mt-1">•</span>
                            <span>Enables us to create better wedding experiences for others</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="heading text-3xl font-bold text-center text-stone-800 mb-12">What Our Customers Say</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-stone-50 p-6 rounded-xl">
                    <div class="flex mb-4">
                        <span class="text-amber-400">⭐⭐⭐⭐⭐</span>
                    </div>
                    <p class="text-stone-600 mb-4">"Amazing platform! Made our wedding planning so much easier. The vendors were professional and reliable."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-rose-200 rounded-full flex items-center justify-center mr-3">
                            <span class="text-rose-600 font-semibold">RD</span>
                        </div>
                        <div>
                            <p class="font-semibold text-stone-800">Rita Desai</p>
                            <p class="text-sm text-stone-500">Nadiad</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-stone-50 p-6 rounded-xl">
                    <div class="flex mb-4">
                        <span class="text-amber-400">⭐⭐⭐⭐⭐</span>
                    </div>
                    <p class="text-stone-600 mb-4">"Excellent service! The package deals were perfect for our budget. Customer support was very helpful throughout."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-amber-200 rounded-full flex items-center justify-center mr-3">
                            <span class="text-amber-600 font-semibold">AP</span>
                        </div>
                        <div>
                            <p class="font-semibold text-stone-800">Amit Patel</p>
                            <p class="text-sm text-stone-500">Anand</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-stone-50 p-6 rounded-xl">
                    <div class="flex mb-4">
                        <span class="text-amber-400">⭐⭐⭐⭐</span>
                    </div>
                    <p class="text-stone-600 mb-4">"Good experience overall. Would love to see more vendor options. The booking process was smooth and transparent."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-stone-200 rounded-full flex items-center justify-center mr-3">
                            <span class="text-stone-600 font-semibold">SM</span>
                        </div>
                        <div>
                            <p class="font-semibold text-stone-800">Sneha Mehta</p>
                            <p class="text-sm text-stone-500">Nadiad</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
