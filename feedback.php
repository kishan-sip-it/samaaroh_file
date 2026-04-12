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
        html { scroll-behavior: smooth; }
        .star-rating { display: flex; gap: 0.25rem; flex-direction: row-reverse; justify-content: flex-end; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { 
            cursor: pointer; 
            font-size: 1.5rem; 
            color: #d1d5db; 
            transition: all 0.2s ease;
            padding: 0.25rem;
            border-radius: 0.25rem;
            order: 1;
        }
        .star-rating label:hover { color: #fbbf24; }
        .star-rating input[type="radio"]:checked ~ label { color: #fbbf24; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Share Your Experience</h1>
        <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
            Your feedback helps us improve and assists other families in making informed wedding planning decisions.
        </p>
    </div>

    <!-- Alert -->
    <?php echo $message; ?>

    <!-- Feedback Form -->
    <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-2">Your Name *</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-2">Email Address *</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="john@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 mb-2">Phone Number</label>
                <input type="tel" id="phone" name="phone"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="service_type" class="block text-sm font-medium text-stone-700 mb-2">Service Used</label>
                    <select id="service_type" name="service_type"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                        <option value="">Select service</option>
                        <option value="bagiwala" <?= ($_POST['service_type'] ?? '') === 'bagiwala' ? 'selected' : '' ?>>Bagiwala</option>
                        <option value="party-plot" <?= ($_POST['service_type'] ?? '') === 'party-plot' ? 'selected' : '' ?>>Party Plot</option>
                        <option value="catering" <?= ($_POST['service_type'] ?? '') === 'catering' ? 'selected' : '' ?>>Catering</option>
                        <option value="photography" <?= ($_POST['service_type'] ?? '') === 'photography' ? 'selected' : '' ?>>Photography</option>
                        <option value="decoration" <?= ($_POST['service_type'] ?? '') === 'decoration' ? 'selected' : '' ?>>Decoration</option>
                        <option value="music" <?= ($_POST['service_type'] ?? '') === 'music' ? 'selected' : '' ?>>Music & DJ</option>
                    </select>
                </div>
                <div>
                    <label for="feedback_type" class="block text-sm font-medium text-stone-700 mb-2">Feedback Type</label>
                    <select id="feedback_type" name="feedback_type"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                        <option value="">Select type</option>
                        <option value="compliment" <?= ($_POST['feedback_type'] ?? '') === 'compliment' ? 'selected' : '' ?>>Compliment</option>
                        <option value="suggestion" <?= ($_POST['feedback_type'] ?? '') === 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                        <option value="complaint" <?= ($_POST['feedback_type'] ?? '') === 'complaint' ? 'selected' : '' ?>>Complaint</option>
                        <option value="review" <?= ($_POST['feedback_type'] ?? '') === 'review' ? 'selected' : '' ?>>Review</option>
                    </select>
                </div>
            </div>
            
            <!-- Star Rating -->
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-2">Overall Rating *</label>
                <div class="star-rating flex-row-reverse justify-end">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1">★</label>
                </div>
                <p class="text-sm text-stone-500 mt-1">Click to rate your experience</p>
            </div>
            
            <div>
                <label for="comments" class="block text-sm font-medium text-stone-700 mb-2">Your Feedback *</label>
                <textarea id="comments" name="comments" required rows="5"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition"
                    placeholder="Share your detailed experience..."><?= htmlspecialchars($_POST['comments'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-2">Would you recommend Samaaroh?</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="recommend" value="yes" class="mr-2">
                        <span>Yes, definitely!</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="recommend" value="no" class="mr-2">
                        <span>Not really</span>
                    </label>
                </div>
            </div>
            
            <button type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-semibold text-lg transition transform hover:scale-105">
                Submit Feedback
            </button>
        </form>
    </div>

    <!-- Thank You Section -->
    <div class="mt-16 text-center">
        <div class="bg-gradient-to-br from-rose-50 to-amber-50 rounded-2xl p-8">
            <div class="text-6xl mb-4">🙏</div>
            <h2 class="heading text-2xl font-bold text-stone-800 mb-4">Thank You for Your Feedback!</h2>
            <p class="text-stone-600 max-w-2xl mx-auto">
                Your honest feedback helps us serve Nadiad families better and improve our wedding planning platform. 
                We read every submission and use it to make meaningful improvements.
            </p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
