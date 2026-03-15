<?php
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Login required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($booking_id <= 0) {
    setAlert("Invalid booking", "error");
    header("Location: " . BASE_URL . "customer/my_bookings.php");
    exit();
}

// FETCH BOOKING (MUST BE ACCEPTED STATUS)
$stmt = $pdo->prepare("
    SELECT b.*, p.name as package_name, p.total_price 
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.id
    WHERE b.id = ? AND b.customer_id = ? AND b.status = 'accepted'
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setAlert("Booking not found or already paid", "error");
    header("Location: " . BASE_URL . "customer/my_bookings.php");
    exit();
}

$advance_amount = round($booking['total_price'] * 0.4);
$provider_share = round($advance_amount * 0.75); // 30% of total (75% of advance)
$platform_fee = round($advance_amount * 0.25); // 10% of total (25% of advance)

// HANDLE PAYMENT SUBMISSION (SIMULATED)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // UPDATE BOOKING: Status = confirmed, advance paid with split details
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'confirmed', 
                advance_paid = 1,
                advance_amount = ?,
                provider_share = ?,
                platform_fee = ?,
                payment_date = NOW()
            WHERE id = ? AND customer_id = ?
        ");
        $stmt->execute([$advance_amount, $provider_share, $platform_fee, $booking_id, $_SESSION['user_id']]);
        
        setAlert("✅ Advance payment of ₹" . number_format($advance_amount, 0) . " received! (₹" . number_format($provider_share, 0) . " to provider, ₹" . number_format($platform_fee, 0) . " platform fee). Wedding date locked.", "success");
        
        // Set donation message flag for flash display
        $_SESSION['show_donation_message'] = true;
        
        header("Location: " . BASE_URL . "customer/my_bookings.php");
        exit();
        
    } catch (PDOException $e) {
        error_log("Advance payment error: " . $e->getMessage());
        setAlert("Payment failed. Contact support.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Advance | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">🔒</span>
            </div>
            <h1 class="text-2xl font-bold text-stone-800">Confirm Wedding Date</h1>
            <p class="text-stone-600">Pay 40% advance to lock your date</p>
        </div>
        
        <?php displayAlert(); ?>
        
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
            <p class="text-sm text-amber-800 font-medium">
                ⚠️ <strong>DATE LOCKED AFTER PAYMENT:</strong> No changes allowed after advance payment. Prevents scams and double-booking.
            </p>
        </div>
        
        <div class="space-y-4 mb-6">
            <div class="flex justify-between">
                <span class="text-stone-600">Package</span>
                <span class="font-medium"><?= htmlspecialchars($booking['package_name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-stone-600">Wedding Date</span>
                <span class="font-medium"><?= date('M d, Y', strtotime($booking['event_date'])) ?></span>
            </div>
            <div class="flex justify-between pt-3 border-t border-stone-200">
                <span class="font-bold text-stone-800">Advance Amount (40%)</span>
                <span class="font-bold text-rose-600 text-xl">₹<?= number_format($advance_amount, 0) ?></span>
            </div>
            <div class="bg-green-50 rounded-lg p-3 mt-3">
                <p class="text-xs text-green-700 font-medium mb-2">💰 Payment Split:</p>
                <div class="space-y-1 text-xs text-green-600">
                    <div class="flex justify-between">
                        <span>Provider gets (30%):</span>
                        <span class="font-bold">₹<?= number_format($provider_share, 0) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Platform fee (10%):</span>
                        <span class="font-bold">₹<?= number_format($platform_fee, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 rounded-xl p-4 mb-6">
            <h3 class="font-bold text-blue-800 mb-2">Payment Methods (Demo)</h3>
            <div class="space-y-2 text-sm text-blue-700">
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>UPI:</strong> samaaroh@pay (Scan QR below)</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Card:</strong> Enter details below (simulated)</span>
                </div>
                <div class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Net Banking:</strong> Select bank (simulated)</span>
                </div>
            </div>
            
            <div class="mt-4 bg-white border-2 border-dashed border-blue-300 rounded-lg h-32 flex items-center justify-center text-blue-400">
                [ QR CODE PLACEHOLDER - FOR DEMO ONLY ]
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
                <p class="text-xs text-stone-400 mt-1">Enter any demo ID for presentation</p>
            </div>
            
            <div class="flex items-start">
                <input type="checkbox" id="agree" required class="mt-1 h-4 w-4 text-rose-600">
                <label for="agree" class="ml-2 text-sm text-stone-700">
                    I confirm this payment locks my wedding date. No changes allowed after payment. 
                    Remaining 70% due within 3 days after wedding completion.
                </label>
            </div>
            
            <button type="submit" 
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl text-lg transition">
                Pay ₹<?= number_format($advance_amount, 0) ?> Advance Now
            </button>
            
            <a href="<?= BASE_URL ?>customer/my_bookings.php" 
               class="block text-center text-stone-600 hover:text-rose-600 font-medium mt-2">
                ← Back to Bookings
            </a>
        </form>
    </div>
</body>
</html>