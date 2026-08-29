<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access booking payment", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    setAlert("Invalid booking ID", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, u.name as provider_name, u.phone as provider_phone
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON s.provider_id = u.id
    WHERE b.id = ? AND b.customer_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setAlert("Booking not found", "error");
    header("Location: " . BASE_URL . "customer/dashboard.php");
    exit();
}

// Handle payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_type = $_POST['payment_type'] ?? '';
    
    if ($payment_type === 'advance') {
        // 40% advance payment
        $advance_amount = $booking['total_price'] * 0.4;
        $donation_amount = $advance_amount * 0.01; // 1% donation
        
        try {
            // Update booking with advance payment
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET advance_paid = ?, advance_amount = ?, donation_amount = ?, status = 'paid'
                WHERE id = ?
            ");
            $stmt->execute([1, $advance_amount, $donation_amount, $booking_id]);
            
            setAlert("✅ Advance payment of ₹" . number_format($advance_amount, 0) . " processed successfully! ₹" . number_format($donation_amount, 0) . " has been donated to Indian Army welfare fund. Booking confirmed!", "success");
            header("Location: " . BASE_URL . "customer/dashboard.php");
            exit();
        } catch (PDOException $e) {
            error_log("Payment error: " . $e->getMessage());
            setAlert("Payment failed. Please try again.", "error");
        }
    } elseif ($payment_type === 'final') {
        // Final payment (60% remaining)
        $final_amount = $booking['total_price'] * 0.6;
        
        try {
            // Update booking with final payment
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET final_paid = ?, final_amount = ?, status = 'completed'
                WHERE id = ?
            ");
            $stmt->execute([1, $final_amount, $booking_id]);
            
            setAlert("✅ Final payment of ₹" . number_format($final_amount, 0) . " processed successfully! Your booking is now complete. Thank you for choosing Samaaroh!", "success");
            header("Location: " . BASE_URL . "customer/dashboard.php");
            exit();
        } catch (PDOException $e) {
            error_log("Payment error: " . $e->getMessage());
            setAlert("Payment failed. Please try again.", "error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Payment | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include '../includes/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <?php displayAlert(); ?>

    <!-- Booking Confirmation Header -->
    <div class="text-center mb-12">
        <div class="flex justify-center mb-4">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-4xl">✅</span>
            </div>
        </div>
        <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800 mb-4">Booking Confirmed!</h1>
        <p class="text-stone-600 max-w-2xl mx-auto">
            <?= htmlspecialchars($booking['provider_name']) ?> has accepted your booking request. 
            Complete the payment to lock your wedding date.
        </p>
    </div>

    <!-- Booking Details -->
    <div class="bg-white rounded-2xl border border-stone-200 p-8 mb-8">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Booking Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-bold text-lg text-stone-800 mb-4">Service Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-stone-600">Service:</span>
                        <span class="font-medium text-stone-900"><?= htmlspecialchars($booking['service_title']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Category:</span>
                        <span class="font-medium text-stone-900"><?= ucfirst($booking['category']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Provider:</span>
                        <span class="font-medium text-stone-900"><?= htmlspecialchars($booking['provider_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Contact:</span>
                        <span class="font-medium text-stone-900"><?= htmlspecialchars($booking['provider_phone']) ?></span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="font-bold text-lg text-stone-800 mb-4">Event Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-stone-600">Event Date:</span>
                        <span class="font-medium text-stone-900"><?= $booking['event_date'] ? date('M j, Y', strtotime($booking['event_date'])) : 'Not specified' ?></span>
                    </div>
                    <?php if ($booking['category'] === 'catering'): ?>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Guest Count:</span>
                        <span class="font-medium text-stone-900"><?= $booking['guest_count'] ?> guests</span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Total Price:</span>
                        <span class="font-bold text-xl text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-600">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Confirmed
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Options -->
    <?php if ($booking['status'] !== 'paid' && $booking['status'] !== 'completed'): ?>
        <!-- Advance Payment -->
        <div class="bg-gradient-to-br from-rose-50 to-amber-50 rounded-2xl p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="heading text-2xl font-bold text-stone-800">Advance Payment</h2>
                    <p class="text-stone-600 mt-2">Pay 40% advance to lock your wedding date</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-rose-600">₹<?= number_format($booking['total_price'] * 0.4, 0) ?></p>
                    <p class="text-stone-500 text-sm">40% of total</p>
                </div>
            </div>
            
            <!-- Social Impact Message -->
            <div class="bg-white rounded-xl p-6 mb-6 border border-amber-200">
                <div class="flex items-center mb-3">
                    <span class="text-3xl mr-3">🇮🇳</span>
                    <h3 class="font-bold text-lg text-stone-800">Social Impact Initiative</h3>
                </div>
                <p class="text-stone-600 mb-3">
                    <strong>1% of your advance payment (₹<?= number_format($booking['total_price'] * 0.4 * 0.01, 0) ?>)</strong> will be donated to the Indian Army Welfare Fund
                </p>
                <p class="text-stone-500 text-sm">
                    Your contribution helps support the families of our brave soldiers who protect our nation. 
                    Together, we build a stronger India while celebrating your special day.
                </p>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="payment_type" value="advance">
                
                <div class="bg-white rounded-xl p-6">
                    <h3 class="font-bold text-lg text-stone-800 mb-4">Payment Method</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="border-2 border-stone-300 rounded-xl p-4 cursor-pointer hover:border-rose-500 transition">
                            <input type="radio" name="method" value="upi" class="sr-only peer" checked>
                            <div class="text-center peer-checked:bg-rose-50 peer-checked:border-rose-500 rounded-lg p-2">
                                <span class="text-2xl">📱</span>
                                <p class="font-medium">UPI</p>
                                <p class="text-xs text-stone-500">GPay, PhonePe</p>
                            </div>
                        </label>
                        
                        <label class="border-2 border-stone-300 rounded-xl p-4 cursor-pointer hover:border-rose-500 transition">
                            <input type="radio" name="method" value="card" class="sr-only peer">
                            <div class="text-center peer-checked:bg-rose-50 peer-checked:border-rose-500 rounded-lg p-2">
                                <span class="text-2xl">💳</span>
                                <p class="font-medium">Card</p>
                                <p class="text-xs text-stone-500">Credit/Debit</p>
                            </div>
                        </label>
                        
                        <label class="border-2 border-stone-300 rounded-xl p-4 cursor-pointer hover:border-rose-500 transition">
                            <input type="radio" name="method" value="net" class="sr-only peer">
                            <div class="text-center peer-checked:bg-rose-50 peer-checked:border-rose-500 rounded-lg p-2">
                                <span class="text-2xl">🏦</span>
                                <p class="font-medium">Net Banking</p>
                                <p class="text-xs text-stone-500">All Banks</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <button type="submit" 
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-xl font-bold text-lg transition transform hover:scale-105">
                    Pay Advance ₹<?= number_format($booking['total_price'] * 0.4, 0) ?>
                </button>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if ($booking['status'] === 'paid'): ?>
        <!-- Final Payment -->
        <div class="bg-stone-900 text-white rounded-2xl p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="heading text-2xl font-bold text-white">Final Payment</h2>
                    <p class="text-stone-300 mt-2">Pay remaining 60% after service completion</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-amber-300">₹<?= number_format($booking['total_price'] * 0.6, 0) ?></p>
                    <p class="text-stone-400 text-sm">60% of total</p>
                </div>
            </div>
            
            <div class="bg-stone-800 rounded-xl p-6 mb-6">
                <div class="flex items-center mb-3">
                    <span class="text-2xl mr-3">✅</span>
                    <h3 class="font-bold text-lg text-white">Advance Payment Completed</h3>
                </div>
                <p class="text-stone-300">
                    Your advance payment of ₹<?= number_format($booking['total_price'] * 0.4, 0) ?> has been received. 
                    Thank you for your contribution to Indian Army Welfare Fund.
                </p>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="payment_type" value="final">
                
                <button type="submit" 
                    class="w-full bg-amber-500 hover:bg-amber-400 text-stone-900 py-4 rounded-xl font-bold text-lg transition transform hover:scale-105">
                    Pay Final Amount ₹<?= number_format($booking['total_price'] * 0.6, 0) ?>
                </button>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if ($booking['status'] === 'completed'): ?>
        <!-- Completed -->
        <div class="bg-green-50 rounded-2xl p-8 text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="heading text-2xl font-bold text-green-800 mb-4">Booking Complete!</h2>
            <p class="text-green-600">
                Thank you for choosing Samaaroh! Your booking has been fully paid and confirmed.
                Wishing you a wonderful wedding celebration!
            </p>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="mt-8 text-center">
        <p class="text-stone-600 mb-4">Need help with payment?</p>
        <a href="<?= BASE_URL ?>contact.php" class="inline-block bg-stone-200 text-stone-700 px-6 py-3 rounded-xl font-semibold hover:bg-stone-300 transition">
            Contact Support
        </a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
