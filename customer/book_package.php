<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to book packages", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// VALIDATE PACKAGE ID
$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
if ($package_id <= 0) {
    setAlert("Invalid package selection", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// FETCH PACKAGE DETAILS
$stmt = $pdo->prepare("SELECT id, name, total_price, description FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    setAlert("Package not found. Please select a valid package.", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// HANDLE BOOKING SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_date = trim($_POST['event_date']);
    $min_date = date('Y-m-d', strtotime('+30 days')); // 30 days min for packages
    
    // VALIDATE DATE
    if (strtotime($event_date) < strtotime($min_date)) {
        setAlert("Package bookings require minimum 30 days notice. Select date after " . date('M d, Y', strtotime($min_date)), "error");
        header("Location: " . BASE_URL . "customer/book_package.php?package_id=$package_id");
        exit();
    }
    
    // CREATE BOOKING (status = pending, package_id set)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (customer_id, package_id, total_price, event_date, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $package_id,
            $package['total_price'],
            $event_date
        ]);
        
        // SUCCESS: Redirect to bookings page with confirmation
        setAlert("✅ Booking request for '" . htmlspecialchars($package['name']) . "' sent! Provider has 12 hours to accept. You'll pay 30% advance after acceptance.", "success");
        header("Location: " . BASE_URL . "customer/my_bookings.php");
        exit();
        
    } catch (PDOException $e) {
        error_log("Package booking error: " . $e->getMessage());
        setAlert("System error. Please try again later.", "error");
        header("Location: " . BASE_URL . "customer/book_package.php?package_id=$package_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}.heading{font-family:'Playfair Display',serif}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-rose-600 to-amber-500 p-6 text-center">
            <h1 class="heading text-2xl font-bold text-white">Book Wedding Package</h1>
            <p class="text-amber-100 mt-1"><?= htmlspecialchars($package['name']) ?></p>
        </div>
        
        <div class="p-6 md:p-8">
            <?php displayAlert(); ?>
            
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                <p class="text-sm text-amber-800">
                    💡 <strong>Important:</strong> After provider acceptance, you'll pay <span class="font-bold">30% advance</span> to confirm date. 
                    Remaining 70% due after wedding completion. Date locked after advance payment.
                </p>
            </div>
            
            <div class="bg-white border border-stone-200 rounded-xl p-5 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($package['name']) ?></h2>
                        <p class="text-stone-600 mt-1 text-sm line-clamp-2"><?= htmlspecialchars($package['description']) ?></p>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-2xl text-rose-600">₹<?= number_format($package['total_price'], 0) ?></div>
                        <div class="text-xs text-stone-400">Total Package Value</div>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Wedding Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="event_date" required 
                           min="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <p class="text-xs text-stone-400 mt-1">Minimum 30 days notice required for packages</p>
                </div>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-bold text-sm text-blue-800 mb-2">🔒 Date Protection Policy</h3>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Date locked immediately after 30% advance payment</li>
                        <li>• No changes allowed after advance payment (prevents scams)</li>
                        <li>• Full refund if provider cancels after acceptance</li>
                        <li>• Remaining 70% due within 3 days after wedding</li>
                    </ul>
                </div>
                
                <button type="submit" 
                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl text-lg transition flex items-center justify-center gap-2">
                    <span>✓ Request Booking</span>
                </button>
                
                <a href="<?= BASE_URL ?>customer/dashboard.php" 
                   class="block text-center text-stone-600 hover:text-rose-600 font-medium mt-2">
                    ← Back to Dashboard
                </a>
            </form>
        </div>
    </div>
</body>
</html>