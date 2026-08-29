<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login as provider to access dashboard", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Handle booking acceptance/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];
    
    try {
        if ($action === 'accept') {
            // Accept booking
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'confirmed'
                WHERE id = ? AND service_id IN (SELECT id FROM services WHERE provider_id = ?)
            ");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            setAlert("✅ Booking accepted! Customer will be notified to make payment.", "success");
        } elseif ($action === 'reject') {
            // Reject booking
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'cancelled'
                WHERE id = ? AND service_id IN (SELECT id FROM services WHERE provider_id = ?)
            ");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            setAlert("Booking rejected. Customer has been notified.", "info");
        }
    } catch (PDOException $e) {
        error_log("Booking action error: " . $e->getMessage());
        setAlert("Failed to update booking. Please try again.", "error");
    }
    
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// Fetch provider's services
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE provider_id = ? 
    ORDER BY id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll();

// Fetch booking requests for provider's services
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, u.name as customer_name, 
           u.email as customer_email, u.phone as customer_phone
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE s.provider_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard | Samaaroh</title>
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

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php displayAlert(); ?>

    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="heading text-3xl font-bold text-stone-800">
            Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! 🎪
        </h1>
        <p class="text-stone-600 mt-2">
            Manage your wedding services and respond to booking requests from customers in Nadiad.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-rose-600 text-xl">📋</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= count($services) ?></p>
                    <p class="text-stone-600 text-sm">Your Services</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-amber-600 text-xl">⏰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        <?= count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; })) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Pending Requests</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-green-600 text-xl">✅</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        <?= count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; })) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Confirmed</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-blue-600 text-xl">💰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">
                        ₹<?= number_format(array_sum(array_column($bookings, 'total_price')), 0) ?>
                    </p>
                    <p class="text-stone-600 text-sm">Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Requests -->
    <div class="mb-8">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Booking Requests</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="font-bold text-xl text-stone-800 mb-2">No booking requests yet</h3>
                <p class="text-stone-600 mb-6">Customers will start booking your services soon!</p>
            </div>
        <?php else: ?>
            <!-- Compact View (First 3 bookings) -->
            <div class="space-y-6 mb-6">
                <?php 
                $display_bookings = array_slice($bookings, 0, 3);
                foreach ($display_bookings as $booking): 
                ?>
                    <div class="bg-white rounded-xl border border-stone-200 p-6 hover:shadow-lg transition">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <!-- Booking Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl">
                                        <?php
                                        $icons = [
                                            'bagiwala' => '🛺',
                                            'party-plot' => '🎪',
                                            'catering' => '🍲',
                                            'photography' => '📸',
                                            'decoration' => '🎨',
                                            'music' => '🎵'
                                        ];
                                        echo $icons[$booking['category']] ?? '🎪';
                                        ?>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($booking['service_title']) ?></h3>
                                        <p class="text-stone-500 text-sm"><?= ucfirst($booking['category']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-stone-500">Customer</p>
                                        <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['customer_name']) ?></p>
                                        <p class="text-stone-500"><?= htmlspecialchars($booking['customer_phone']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-stone-500">Event Details</p>
                                        <p class="font-medium text-stone-900"><?= $booking['event_date'] ? date('M j, Y', strtotime($booking['event_date'])) : 'Not specified' ?></p>
                                        <p class="text-stone-500"><?= $booking['guest_count'] ?> guests</p>
                                    </div>
                                    <div>
                                        <p class="text-stone-500">Revenue</p>
                                        <p class="font-bold text-lg text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status & Actions -->
                            <div class="flex flex-col items-end gap-3">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                    <?php
                                    switch($booking['status']) {
                                        case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                        case 'pending': echo 'bg-amber-100 text-amber-800'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                        case 'advance_paid': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'completed': echo 'bg-stone-100 text-stone-800'; break;
                                        default: echo 'bg-stone-100 text-stone-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                </span>
                                
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="flex gap-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                                Accept
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Show All Button -->
            <?php if (count($bookings) > 3): ?>
                <div class="text-center">
                    <button onclick="openBookingDrawer()" 
                            class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Show All (<?= count($bookings) ?> Bookings)
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Booking Drawer -->
    <div id="bookingDrawer" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeBookingDrawer()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white shadow-xl transform transition-transform duration-300 translate-x-full" id="drawerContent">
            <div class="h-full flex flex-col">
                <!-- Drawer Header -->
                <div class="bg-stone-800 text-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="heading text-xl font-bold">All Booking Requests (<?= count($bookings) ?>)</h2>
                        <button onclick="closeBookingDrawer()" class="text-white hover:text-stone-300 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="space-y-6">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="bg-stone-50 rounded-xl border border-stone-200 p-6 hover:shadow-lg transition">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <!-- Booking Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-2xl">
                                                <?php
                                                $icons = [
                                                    'bagiwala' => '🛺',
                                                    'party-plot' => '🎪',
                                                    'catering' => '🍲',
                                                    'photography' => '📸',
                                                    'decoration' => '🎨',
                                                    'music' => '🎵'
                                                ];
                                                echo $icons[$booking['category']] ?? '🎪';
                                                ?>
                                            </span>
                                            <div>
                                                <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($booking['service_title']) ?></h3>
                                                <p class="text-stone-500 text-sm"><?= ucfirst($booking['category']) ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <p class="text-stone-500">Customer</p>
                                                <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['customer_name']) ?></p>
                                                <p class="text-stone-500"><?= htmlspecialchars($booking['customer_phone']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-stone-500">Event Details</p>
                                                <p class="font-medium text-stone-900"><?= $booking['event_date'] ? date('M j, Y', strtotime($booking['event_date'])) : 'Not specified' ?></p>
                                                <p class="text-stone-500"><?= $booking['guest_count'] ?> guests</p>
                                            </div>
                                            <div>
                                                <p class="text-stone-500">Revenue</p>
                                                <p class="font-bold text-lg text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status & Actions -->
                                    <div class="flex flex-col items-end gap-3">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                            <?php
                                            switch($booking['status']) {
                                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                                case 'pending': echo 'bg-amber-100 text-amber-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'advance_paid': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'completed': echo 'bg-stone-100 text-stone-800'; break;
                                                default: echo 'bg-stone-100 text-stone-800';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                        </span>
                                        
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <div class="flex gap-2">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" 
                                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                                        Accept
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" 
                                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBookingDrawer() {
            const drawer = document.getElementById('bookingDrawer');
            const drawerContent = document.getElementById('drawerContent');
            drawer.classList.remove('hidden');
            setTimeout(() => {
                drawerContent.classList.remove('translate-x-full');
            }, 10);
        }
        
        function closeBookingDrawer() {
            const drawer = document.getElementById('bookingDrawer');
            const drawerContent = document.getElementById('drawerContent');
            drawerContent.classList.add('translate-x-full');
            setTimeout(() => {
                drawer.classList.add('hidden');
            }, 300);
        }
    </script>

    <!-- Your Services -->
    <div>
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Your Services</h2>
        
        <?php if (empty($services)): ?>
            <div class="bg-white rounded-xl border border-stone-200 p-8 text-center">
                <div class="text-6xl mb-4">🎪</div>
                <h3 class="font-bold text-xl text-stone-800 mb-2">No services listed yet</h3>
                <p class="text-stone-600 mb-6">Add your wedding services to start receiving bookings!</p>
                <a href="<?= BASE_URL ?>provider/add_service.php" class="inline-block bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Add Service
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($services as $service): ?>
                    <div class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-lg transition">
                        <!-- Service Image -->
                        <div class="h-48 bg-stone-100 relative">
                            <?php if (!empty($service['image_path'])): ?>
                                <img src="<?= UPLOADS_URL . htmlspecialchars($service['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($service['title']) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-4xl">
                                        <?php
                                        $icons = [
                                            'bagiwala' => ' ',
                                            'party-plot' => ' ',
                                            'catering' => ' ',
                                            'photography' => ' ',
                                            'decoration' => ' ',
                                            'music' => ' '
                                        ];
                                        echo $icons[$service['category']] ?? ' ';
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= $service['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $service['is_available'] ? 'Available' : 'Unavailable' ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Service Info -->
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($service['title']) ?></h3>
                                    <p class="text-stone-500 text-sm"><?= ucfirst($service['category']) ?></p>
                                </div>
                            </div>
                            
                            <p class="text-stone-600 text-sm mb-4">
                                <?= htmlspecialchars(substr($service['description'], 0, 100)) ?>...
                            </p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <p class="text-xl font-bold text-stone-900">Rs.<?= number_format($service['price'], 0) ?></p>
                                    <p class="text-stone-500 text-xs">per service</p>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <form method="GET" action="<?= BASE_URL ?>provider/edit_service.php" class="flex-1">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                </form>
                                
                                <form method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <button type="submit" name="delete_service" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
