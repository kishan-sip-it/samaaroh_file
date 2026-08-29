<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access profile", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (!empty($name) && !empty($email) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
            
            // Update session name
            $_SESSION['name'] = $name;
            
            setAlert("Profile updated successfully!", "success");
            header("Location: " . BASE_URL . "customer/profile.php");
            exit();
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            setAlert("Failed to update profile. Please try again.", "error");
        }
    } else {
        setAlert("Please fill in all required fields.", "error");
    }
}

// Fetch customer profile
$stmt = $pdo->prepare("
    SELECT u.*, COUNT(b.id) as booking_count, COALESCE(SUM(b.total_price), 0) as total_spent
    FROM users u
    LEFT JOIN bookings b ON u.id = b.customer_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

// Fetch customer's recent bookings
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.category, u.name as provider_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON s.provider_id = u.id
    WHERE b.customer_id = ?
    ORDER BY b.id DESC
    LIMIT 5
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
    <title>Customer Profile | Samaaroh</title>
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

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <?php displayAlert(); ?>

    <!-- Profile Header -->
    <div class="bg-white rounded-2xl border border-stone-200 p-8 mb-8 shadow-sm">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <!-- Profile Picture -->
            <div class="flex-shrink-0">
                <div class="w-32 h-32 bg-gradient-to-br from-rose-100 to-amber-100 rounded-full flex items-center justify-center">
                    <span class="text-5xl font-bold text-rose-600">
                        <?= substr(strtoupper($profile['name']), 0, 2) ?>
                    </span>
                </div>
            </div>
            
            <!-- Profile Info -->
            <div class="flex-1 text-center md:text-left">
                <h1 class="heading text-3xl font-bold text-stone-800 mb-2">
                    <?= htmlspecialchars($profile['name']) ?>
                </h1>
                <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-4">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        Customer
                    </span>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">
                        Verified
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-stone-500">Email</span>
                        <span class="font-medium text-stone-800"><?= htmlspecialchars($profile['email']) ?></span>
                    </div>
                    <div>
                        <span class="text-stone-500">Phone</span>
                        <span class="font-medium text-stone-800"><?= htmlspecialchars($profile['phone']) ?></span>
                    </div>
                    <div>
                        <span class="text-stone-500">Bookings</span>
                        <span class="font-medium text-stone-800"><?= $profile['booking_count'] ?> total</span>
                    </div>
                    <div>
                        <span class="text-stone-500">Member Since</span>
                        <span class="font-medium text-stone-800">
                            <?php
                            $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $created = $stmt->fetchColumn();
                            echo date('M Y', strtotime($created));
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-rose-600 text-xl">📋</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= $profile['booking_count'] ?></p>
                    <p class="text-stone-600 text-sm">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-green-600 text-xl">💰</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800">₹<?= number_format($profile['total_spent'], 0) ?></p>
                    <p class="text-stone-600 text-sm">Total Spent</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                    <span class="text-amber-600 text-xl">🎪</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-stone-800"><?= count($bookings) ?></p>
                    <p class="text-stone-600 text-sm">Active Services</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm mb-8">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Edit Profile</h2>
        
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        value="<?= htmlspecialchars($profile['name']) ?>">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        value="<?= htmlspecialchars($profile['email']) ?>">
                </div>
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 mb-2">Phone Number</label>
                <input type="tel" id="phone" name="phone" required
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    value="<?= htmlspecialchars($profile['phone']) ?>">
            </div>
            
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-xl font-semibold transition">
                    Update Profile
                </button>
                <a href="<?= BASE_URL ?>customer/dashboard.php" 
                    class="bg-stone-200 hover:bg-stone-300 text-stone-700 px-8 py-3 rounded-xl font-semibold transition">
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>

    <!-- Recent Bookings -->
    <div>
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Recent Bookings</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                <div class="text-6xl mb-4">🎪</div>
                <h3 class="font-bold text-xl text-stone-800 mb-2">No bookings yet</h3>
                <p class="text-stone-600 mb-6">Start exploring wedding services and book your dream wedding!</p>
                <a href="<?= BASE_URL ?>customer/dashboard.php" 
                    class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Browse Services
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-stone-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-200">
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['service_title']) ?></p>
                                            <p class="text-sm text-stone-500"><?= ucfirst($booking['category']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-900"><?= htmlspecialchars($booking['provider_name']) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-stone-900"><?= date('M j, Y') ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-900">₹<?= number_format($booking['total_price'], 0) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
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
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <a href="<?= BASE_URL ?>customer/booking_payment.php?booking_id=<?= $booking['id'] ?>" 
                                               class="text-rose-600 hover:text-rose-900 font-medium">
                                                Make Payment
                                            </a>
                                        <?php elseif ($booking['status'] === 'advance_paid'): ?>
                                            <a href="<?= BASE_URL ?>customer/booking_payment.php?booking_id=<?= $booking['id'] ?>" 
                                               class="text-rose-600 hover:text-rose-900 font-medium">
                                                Final Payment
                                            </a>
                                        <?php else: ?>
                                            <a href="#" class="text-rose-600 hover:text-rose-900 font-medium">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
