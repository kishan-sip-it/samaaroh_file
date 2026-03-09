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
    <title>Report an Issue | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50">
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="bg-stone-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800 mb-4">Report an Issue</h1>
            <p class="text-stone-600 max-w-2xl mx-auto">
                Help us improve Samaaroh by reporting any issues or concerns you encounter
            </p>
        </div>
    </section>

    <!-- Report Form Section -->
    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <?php echo $message; ?>
                
                <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Submit Your Report</h2>
                
                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="John Doe"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-stone-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="john@example.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-stone-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                               placeholder="+91 98765 43210"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="issue_type" class="block text-sm font-medium text-stone-700 mb-2">Issue Type *</label>
                            <select id="issue_type" name="issue_type" required
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Select Issue Type</option>
                                <option value="booking" <?= ($_POST['issue_type'] ?? '') === 'booking' ? 'selected' : '' ?>>Booking Issue</option>
                                <option value="payment" <?= ($_POST['issue_type'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment Problem</option>
                                <option value="vendor" <?= ($_POST['issue_type'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor Complaint</option>
                                <option value="service" <?= ($_POST['issue_type'] ?? '') === 'service' ? 'selected' : '' ?>>Service Quality</option>
                                <option value="technical" <?= ($_POST['issue_type'] ?? '') === 'technical' ? 'selected' : '' ?>>Technical Issue</option>
                                <option value="other" <?= ($_POST['issue_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="priority" class="block text-sm font-medium text-stone-700 mb-2">Priority Level</label>
                            <select id="priority" name="priority"
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="low" <?= ($_POST['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= ($_POST['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= ($_POST['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-stone-700 mb-2">Description *</label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                  placeholder="Please describe the issue in detail..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" name="terms" required class="mr-2">
                        <label for="terms" class="text-sm text-stone-600">
                            I agree to the terms and conditions for submitting this report
                        </label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-4 rounded-xl transition duration-200 shadow-lg hover:shadow-xl">
                        Submit Report
                    </button>
                </form>
                
                <div class="mt-8 pt-6 border-t border-stone-200">
                    <h3 class="font-semibold text-stone-800 mb-3">What happens next?</h3>
                    <ul class="space-y-2 text-sm text-stone-600">
                        <li class="flex items-start">
                            <span class="text-rose-500 mr-2 mt-1">•</span>
                            <span>Your report will be reviewed by our support team within 24 hours</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-rose-500 mr-2 mt-1">•</span>
                            <span>We'll contact you via email or phone for follow-up if needed</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-rose-500 mr-2 mt-1">•</span>
                            <span>Urgent issues will be prioritized and addressed immediately</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
