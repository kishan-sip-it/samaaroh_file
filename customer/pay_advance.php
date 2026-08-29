<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access your dashboard", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($booking_id <= 0) {
    setAlert("Invalid booking", "error");
    header("Location: " . BASE_URL . "customer/my_bookings.php");
    exit();
}

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, u.name as provider_name
    FROM bookings b
    LEFT JOIN services s ON b.service_id = s.id
    LEFT JOIN users u ON s.provider_id = u.id
    WHERE b.id = ? AND b.customer_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setAlert("Booking not found", "error");
    header("Location: " . BASE_URL . "customer/my_bookings.php");
    exit();
}

// Check if booking is in correct status for advance payment
if ($booking['status'] !== 'confirmed' && $booking['status'] !== 'accepted') {
    setAlert("Booking not ready for advance payment", "error");
    header("Location: " . BASE_URL . "customer/my_bookings.php");
    exit();
}

// Calculate payment amounts
$booking_price = $booking['total_price'];
$advance_amount = round($booking_price * 0.4); // 40% advance
$remaining_amount = $booking_price - $advance_amount;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Simple update without complex status changes
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'confirmed',
                advance_amount = ?,
                payment_date = NOW()
            WHERE id = ? AND customer_id = ?
        ");
        $result = $stmt->execute([$advance_amount, $booking_id, $_SESSION['user_id']]);
        
        if ($result) {
            setAlert("✅ Advance payment of ₹" . number_format($advance_amount, 0) . " received! Wedding date locked.", "success");
            
            // Set donation message flag
            $_SESSION['show_donation_message'] = true;
            
            // Redirect to my_bookings page (user can choose to view invoice or dashboard)
            header("Location: " . BASE_URL . "customer/my_bookings.php");
            exit();
        } else {
            setAlert("Payment failed. Please try again.", "error");
        }
        
    } catch (PDOException $e) {
        error_log("Payment error: " . $e->getMessage());
        setAlert("Payment failed. Database error: " . $e->getMessage(), "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <title>Pay Advance | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">💰</span>
            </div>
            <h1 class="text-2xl font-bold text-stone-800">Advance Payment</h1>
            <p class="text-stone-600">Secure your wedding date with advance payment</p>
        </div>
        
        <?php displayAlert(); ?>
        
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
            <p class="text-sm text-green-800 font-medium">
                ✅ <strong>WEDDING DATE LOCKED:</strong> Your advance payment will secure your date with the provider.
            </p>
        </div>
        
        <div class="space-y-4 mb-6">
            <div class="flex justify-between">
                <span class="text-stone-600">Service</span>
                <span class="font-medium"><?= htmlspecialchars($booking['service_title']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-stone-600">Provider</span>
                <span class="font-medium"><?= htmlspecialchars($booking['provider_name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-stone-600">Wedding Date</span>
                <span class="font-medium"><?= date('M d, Y', strtotime($booking['event_date'])) ?></span>
            </div>
            
            <div class="pt-3 border-t border-stone-200">
                <div class="flex justify-between text-sm text-stone-500 mb-2">
                    <span>Total Amount:</span>
                    <span>₹<?= number_format($booking_price, 0) ?></span>
                </div>
                <div class="flex justify-between text-sm text-green-600 mb-2">
                    <span>Advance Payment (40%):</span>
                    <span>-₹<?= number_format($advance_amount, 0) ?></span>
                </div>
                <div class="flex justify-between pt-3 border-t border-stone-200">
                    <span class="font-bold text-stone-800">Advance Amount</span>
                    <span class="font-bold text-rose-600 text-xl">₹<?= number_format($advance_amount, 0) ?></span>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 rounded-xl p-4 mb-6">
            <h3 class="font-bold text-blue-800 mb-2">Payment Methods</h3>
            <div class="space-y-2 text-sm text-blue-700">
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>UPI:</strong> samaaroh@pay</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Card:</strong> Enter details below (demo)</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Net Banking:</strong> Available options (demo)</span>
                </div>
            </div>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Payment Method</label>
                <select name="method" required class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500">
                    <option value="upi">UPI (Recommended)</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="netbanking">Net Banking</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Transaction ID (Demo)</label>
                <input type="text" name="txn_id" required placeholder="TXN123456" 
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500">
                <p class="text-xs text-stone-400 mt-1">Enter any demo ID for testing</p>
            </div>
            
            <div class="flex items-start">
                <input type="checkbox" id="agree" required class="mt-1 h-4 w-4 text-rose-600">
                <label for="agree" class="ml-2 text-sm text-stone-700">
                    I confirm this advance payment to secure my wedding date.
                </label>
            </div>
            
            <button type="submit" 
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl text-lg transition">
                Pay ₹<?= number_format($advance_amount, 0) ?> Advance
            </button>
            
            <a href="<?= BASE_URL ?>customer/my_bookings.php" 
               class="block text-center text-stone-600 hover:text-rose-600 font-medium mt-2">
                ← Back to Bookings
            </a>
        </form>
    </div>
</body>
</html>
