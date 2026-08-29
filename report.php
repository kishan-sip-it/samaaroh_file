<?php
require_once 'config/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $issue_type = $_POST['issue_type'];
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    
    if (!empty($name) && !empty($email) && !empty($issue_type) && !empty($description)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reports (name, email, phone, issue_type, description, priority, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$name, $email, $phone, $issue_type, $description, $priority]);
            
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded'>
                          <strong>Success!</strong> Your report has been submitted. We'll review it within 24 hours.
                        </div>";
        } catch (PDOException $e) {
            error_log("Report submission error: " . $e->getMessage());
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                          <strong>Error!</strong> System error. Please try again later.
                        </div>";
        }
    } else {
        $message = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded'>
                      <strong>Error!</strong> Please fill in all required fields.
                    </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report an Issue | Samaaroh</title>
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

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Report an Issue</h1>
        <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
            Help us improve Samaaroh by reporting any issues or concerns you encounter
        </p>
    </div>

    <!-- Alert -->
    <?php echo $message; ?>

    <!-- Report Form -->
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
                    <label for="issue_type" class="block text-sm font-medium text-stone-700 mb-2">Issue Type *</label>
                    <select id="issue_type" name="issue_type" required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                        <option value="">Select issue type</option>
                        <option value="booking" <?= ($_POST['issue_type'] ?? '') === 'booking' ? 'selected' : '' ?>>Booking Issue</option>
                        <option value="payment" <?= ($_POST['issue_type'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment Problem</option>
                        <option value="service" <?= ($_POST['issue_type'] ?? '') === 'service' ? 'selected' : '' ?>>Service Quality</option>
                        <option value="vendor" <?= ($_POST['issue_type'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor Issue</option>
                        <option value="website" <?= ($_POST['issue_type'] ?? '') === 'website' ? 'selected' : '' ?>>Website Bug</option>
                        <option value="account" <?= ($_POST['issue_type'] ?? '') === 'account' ? 'selected' : '' ?>>Account Problem</option>
                        <option value="other" <?= ($_POST['issue_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-stone-700 mb-2">Priority Level</label>
                    <select id="priority" name="priority"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition">
                        <option value="low" <?= ($_POST['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($_POST['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($_POST['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-stone-700 mb-2">Issue Description *</label>
                <textarea id="description" name="description" required rows="6"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition"
                    placeholder="Please describe the issue in detail..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <button type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-semibold text-lg transition transform hover:scale-105">
                Submit Report
            </button>
        </form>
    </div>

    <!-- Contact Info -->
    <div class="mt-12 bg-stone-900 rounded-2xl p-8 text-white text-center">
        <div class="text-6xl mb-4">🚀</div>
        <h2 class="heading text-2xl font-bold text-white mb-4">We're Here to Help</h2>
        <p class="text-stone-300 max-w-2xl mx-auto mb-6">
            Our support team reviews every report and responds within 24 hours. 
            For urgent wedding-related issues, call us directly at +91 98765 43210.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:+919876543210" 
                class="inline-block bg-amber-500 hover:bg-amber-400 text-stone-900 px-6 py-3 rounded-xl font-semibold transition">
                Call Support
            </a>
            <a href="<?= BASE_URL ?>contact.php" 
                class="inline-block bg-white hover:bg-stone-100 text-stone-900 px-6 py-3 rounded-xl font-semibold transition">
                Contact Us
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
