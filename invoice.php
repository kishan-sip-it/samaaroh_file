<?php
require_once 'config/config.php';

// AUTH CHECK: Must be logged in
if (!isset($_SESSION['user_id'])) {
    setAlert("Please login to view invoices", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// GET BOOKING ID FROM URL
$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    setAlert("Invalid invoice request", "error");
    header("Location: " . BASE_URL);
    exit();
}

// FETCH BOOKING DETAILS WITH PERMISSION CHECK
if ($_SESSION['role'] === 'customer') {
    // Customer can only see their own bookings
    $stmt = $pdo->prepare("
        SELECT b.*, s.title as service_title, s.category, s.price as service_price,
               u.name as provider_name, u.email as provider_email, u.phone as provider_phone
        FROM bookings b
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN users u ON s.provider_id = u.id
        WHERE b.id = ? AND b.customer_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
} elseif ($_SESSION['role'] === 'provider') {
    // Provider can only see bookings for their services
    $stmt = $pdo->prepare("
        SELECT b.*, s.title as service_title, s.category, s.price as service_price,
               c.name as customer_name, c.email as customer_email, c.phone as customer_phone
        FROM bookings b
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN users c ON b.customer_id = c.id
        WHERE b.id = ? AND s.provider_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
} else {
    // Admin can see all bookings
    $stmt = $pdo->prepare("
        SELECT b.*, s.title as service_title, s.category, s.price as service_price,
               u.name as provider_name, u.email as provider_email, u.phone as provider_phone,
               c.name as customer_name, c.email as customer_email, c.phone as customer_phone
        FROM bookings b
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN users u ON s.provider_id = u.id
        LEFT JOIN users c ON b.customer_id = c.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
}

$booking = $stmt->fetch();

if (!$booking) {
    setAlert("Invoice not found or access denied", "error");
    header("Location: " . BASE_URL);
    exit();
}

// CALCULATE PAYMENTS
$advance_amount = ($booking['total_price'] * 40) / 100;
$remaining_amount = ($booking['total_price'] * 60) / 100;
$gst_amount = ($booking['total_price'] * 18) / 100; // 18% GST
$grand_total = $booking['total_price'] + $gst_amount;

// GENERATE INVOICE NUMBER
$invoice_type = $_GET['type'] ?? 'full';
$invoice_prefix = $invoice_type === 'advance' ? 'ADV' : ($invoice_type === 'final' ? 'FIN' : 'SMA');
$invoice_number = $invoice_prefix . '-' . date('Y') . '-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT);

// DETERMINE BILLING PARTY - Show both consumer and provider details
$is_customer_viewing = $_SESSION['role'] === 'customer';
$billing_party = [
    'name' => $is_customer_viewing ? $booking['provider_name'] : $booking['customer_name'],
    'email' => $is_customer_viewing ? $booking['provider_email'] : $booking['customer_email'],
    'phone' => $is_customer_viewing ? $booking['provider_phone'] : $booking['customer_phone'],
    'address' => $booking['address'] ?? 'N/A',
    'gst' => $booking['gst_number'] ?? 'N/A'
];

// Service provider details for reference
$service_provider = [
    'name' => $booking['provider_name'],
    'email' => $booking['provider_email'],
    'phone' => $booking['provider_phone'],
    'address' => $booking['address'] ?? 'N/A',
    'gst' => $booking['gst_number'] ?? 'N/A'
];

// Customer details for reference
$service_customer = [
    'name' => $booking['customer_name'] ?? 'N/A',
    'email' => $booking['customer_email'] ?? 'N/A', 
    'phone' => $booking['customer_phone'] ?? 'N/A',
    'address' => $booking['address'] ?? 'N/A'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $invoice_number ?> | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .invoice-box { 
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
            background: white;
        }
        @media print {
            body { background: white; }
            .no-print { display: none; }
            .invoice-box { box-shadow: none; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Print Button -->
        <div class="no-print mb-6 text-center">
            <button onclick="window.print()" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2 mx-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h2m-6-4h6m2 4h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                </svg>
                Print Invoice
            </button>
        </div>

        <!-- Invoice Container -->
        <div class="invoice-box rounded-lg p-8 bg-white">
            <!-- Header -->
            <div class="border-b-2 border-rose-600 pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <?php
                        $invoice_title = $invoice_type === 'advance' ? 'ADVANCE PAYMENT INVOICE' : 
                                       ($invoice_type === 'final' ? 'FINAL PAYMENT INVOICE' : 'INVOICE');
                        $invoice_color = $invoice_type === 'advance' ? 'text-blue-600' : 
                                       ($invoice_type === 'final' ? 'text-green-600' : 'text-rose-600');
                        ?>
                        <h1 class="heading text-3xl font-bold <?= $invoice_color ?> mb-2"><?= $invoice_title ?></h1>
                        <p class="text-gray-600">Invoice Number: <?= $invoice_number ?></p>
                        <p class="text-gray-600">Date: <?= date('d M, Y') ?></p>
                        <p class="text-gray-600">Booking Date: <?= date('d M, Y', strtotime($booking['booking_date'])) ?></p>
                        <p class="text-gray-600">Wedding Date: <?= date('d M, Y', strtotime($booking['event_date'])) ?></p>
                        <?php if ($invoice_type !== 'full'): ?>
                        <p class="text-sm text-<?= $invoice_color ?> font-medium">
                            <?= $invoice_type === 'advance' ? '40% Advance Payment' : '60% Final Payment' ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <div class="mb-4">
                            <img src="<?= BASE_URL ?>assets/logo.svg" alt="Samaaroh" class="h-16 mx-auto">
                        </div>
                        <p class="text-sm text-gray-600">Samaaroh Wedding Planning</p>
                        <p class="text-sm text-gray-600">support@samaaroh.com</p>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">Billed To:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($billing_party['name']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($billing_party['email']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($billing_party['phone']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($billing_party['address']) ?></p>
                        <?php if ($billing_party['gst'] !== 'N/A'): ?>
                        <p class="text-sm text-gray-600">GST: <?= htmlspecialchars($billing_party['gst']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">Service Provider:</h3>
                    <div class="bg-rose-50 p-4 rounded-lg">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($service_provider['name']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($service_provider['email']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($service_provider['phone']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($service_provider['address']) ?></p>
                        <?php if ($service_provider['gst'] !== 'N/A'): ?>
                        <p class="text-sm text-gray-600">GST: <?= htmlspecialchars($service_provider['gst']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Customer Details (for reference) -->
            <?php if (!$is_customer_viewing): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mb-8">
                <h3 class="font-semibold text-blue-800 mb-3">Customer Details (For Reference):</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name:</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($service_customer['name']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email:</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($service_customer['email']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone:</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($service_customer['phone']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Address:</p>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($service_customer['address']) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Service Details -->
            <div class="mb-8">
                <h3 class="font-semibold text-gray-800 mb-4">Service Details</h3>
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left p-4 font-semibold text-gray-700">Service Description</th>
                                <th class="text-center p-4 font-semibold text-gray-700">Category</th>
                                <th class="text-center p-4 font-semibold text-gray-700">Date</th>
                                <th class="text-right p-4 font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($booking['service_title']) ?></div>
                                    <div class="text-sm text-gray-600 mt-1"><?= nl2br(htmlspecialchars($booking['special_requirements'] ?? 'Standard service')) ?></div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                        <?= ucfirst($booking['category']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center text-gray-600">
                                    <?= date('d M, Y', strtotime($booking['booking_date'])) ?>
                                </td>
                                <td class="p-4 text-right font-medium text-gray-800">
                                    ₹<?= number_format($booking['total_price'], 2) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4">Payment Schedule</h3>
                    <div class="space-y-3">
                        <?php if ($invoice_type === 'advance'): ?>
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border-2 border-blue-200">
                            <div>
                                <p class="font-medium text-gray-800">Advance Payment (40%)</p>
                                <p class="text-sm text-gray-600">Paid - Wedding date locked</p>
                            </div>
                            <p class="font-bold text-blue-700">₹<?= number_format($booking['advance_amount'], 2) ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Remaining Payment (60%)</p>
                                <p class="text-sm text-gray-600">Due before wedding</p>
                            </div>
                            <p class="font-bold text-gray-700">₹<?= number_format($booking['total_price'] - $booking['advance_amount'], 2) ?></p>
                        </div>
                        <?php elseif ($invoice_type === 'final'): ?>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Advance Payment (40%)</p>
                                <p class="text-sm text-gray-600">Previously paid</p>
                            </div>
                            <p class="font-bold text-green-700">₹<?= number_format($booking['advance_amount'], 2) ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border-2 border-green-200">
                            <div>
                                <p class="font-medium text-gray-800">Final Payment (60%)</p>
                                <p class="text-sm text-gray-600">Paid - Booking completed</p>
                            </div>
                            <p class="font-bold text-green-700">₹<?= number_format($booking['final_payment_amount'] ?? ($booking['total_price'] - $booking['advance_amount']), 2) ?></p>
                        </div>
                        <?php else: ?>
                        <div class="flex justify-between items-center p-3 bg-amber-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Advance Payment (40%)</p>
                                <p class="text-sm text-gray-600">Due at booking confirmation</p>
                            </div>
                            <p class="font-bold text-amber-700">₹<?= number_format(($booking['total_price'] * 40) / 100, 2) ?></p>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Remaining Payment (60%)</p>
                                <p class="text-sm text-gray-600">Due on wedding day</p>
                            </div>
                            <p class="font-bold text-blue-700">₹<?= number_format(($booking['total_price'] * 60) / 100, 2) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4">Payment Summary</h3>
                    <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                        <?php if ($invoice_type === 'advance'): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Advance Amount (40%):</span>
                            <span class="font-medium">₹<?= number_format($booking['advance_amount'], 2) ?></span>
                        </div>
                        <?php elseif ($invoice_type === 'final'): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Final Amount (60%):</span>
                            <span class="font-medium">₹<?= number_format($booking['final_payment_amount'] ?? ($booking['total_price'] - $booking['advance_amount']), 2) ?></span>
                        </div>
                        <?php else: ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">₹<?= number_format($booking['total_price'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($invoice_type === 'full'): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">GST (18%):</span>
                            <span class="font-medium">₹<?= number_format($gst_amount, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="border-t pt-2 flex justify-between">
                            <?php
                            if ($invoice_type === 'advance') {
                                $invoice_total = $booking['advance_amount'];
                                $total_label = 'Advance Total:';
                            } elseif ($invoice_type === 'final') {
                                $invoice_total = $booking['final_payment_amount'] ?? ($booking['total_price'] - $booking['advance_amount']);
                                $total_label = 'Final Total:';
                            } else {
                                $invoice_total = $grand_total;
                                $total_label = 'Grand Total:';
                            }
                            ?>
                            <span class="font-semibold text-gray-800"><?= $total_label ?></span>
                            <span class="font-bold text-xl <?= $invoice_color ?>">₹<?= number_format($invoice_total, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status and Notes -->
            <div class="mt-8 pt-6 border-t">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">Payment Status</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full <?= ($booking['status'] === 'confirmed' || $booking['status'] === 'completed' || $booking['advance_paid']) ? 'bg-green-500' : 'bg-yellow-500' ?>"></span>
                                <span class="text-gray-700">Advance Payment (40%): <?= ($booking['status'] === 'confirmed' || $booking['status'] === 'completed' || $booking['advance_paid']) ? 'Paid' : 'Pending' ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full <?= $booking['status'] === 'confirmed' ? 'bg-green-500' : 'bg-yellow-500' ?>"></span>
                                <span class="text-gray-700">Booking Status: <?= ucfirst($booking['status']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">Terms & Conditions</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>• 40% advance payment required to confirm booking</p>
                            <p>• Remaining 60% due on wedding day</p>
                            <p>• Cancellation policy applies as per terms</p>
                            <p>• GST applicable as per government regulations</p>
                            <p>• This invoice is computer generated and valid</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="no-print mt-8 text-center">
            <?php if ($_SESSION['role'] === 'customer'): ?>
                <a href="<?= BASE_URL ?>customer/my_bookings.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-rose-600 transition">
                    <span>←</span>
                    <span>Back to My Bookings</span>
                </a>
            <?php elseif ($_SESSION['role'] === 'provider'): ?>
                <a href="<?= BASE_URL ?>provider/dashboard.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-rose-600 transition">
                    <span>←</span>
                    <span>Back to Dashboard</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>admin/view_reports.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-rose-600 transition">
                    <span>←</span>
                    <span>Back to Admin Panel</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
