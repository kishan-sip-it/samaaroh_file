<?php
require_once '../config/config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login as provider to access profile", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $address = trim($_POST['address']);
    
    if (!empty($name) && !empty($email) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, bio = ?, address = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $bio, $address, $_SESSION['user_id']]);
            
            // Update session name
            $_SESSION['name'] = $name;
            
            setAlert("Profile updated successfully!", "success");
            header("Location: " . BASE_URL . "provider/profile.php");
            exit();
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            setAlert("Failed to update profile. Please try again.", "error");
        }
    } else {
        setAlert("Please fill in all required fields.", "error");
    }
}

// Fetch provider profile
$stmt = $pdo->prepare("
    SELECT u.*, COUNT(s.id) as service_count
    FROM users u
    LEFT JOIN services s ON u.id = s.provider_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

// Fetch provider's services
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE provider_id = ? 
    ORDER BY id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Profile | Samaaroh</title>
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
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Provider
                    </span>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                        <?= $profile['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $profile['is_verified'] ? 'Verified' : 'Unverified' ?>
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
                        <span class="text-stone-500">Services</span>
                        <span class="font-medium text-stone-800"><?= $profile['service_count'] ?> listed</span>
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

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Edit Profile</h2>
        
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-2">Business Name</label>
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
            
            <div>
                <label for="address" class="block text-sm font-medium text-stone-700 mb-2">Business Address</label>
                <textarea id="address" name="address" rows="3"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition"
                    placeholder="Your business address..."><?= htmlspecialchars($profile['address']) ?></textarea>
            </div>
            
            <div>
                <label for="bio" class="block text-sm font-medium text-stone-700 mb-2">About Your Business</label>
                <textarea id="bio" name="bio" rows="4"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition"
                    placeholder="Tell customers about your wedding services..."><?= htmlspecialchars($profile['bio']) ?></textarea>
            </div>
            
            <div class="flex gap-4">
                <button type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-xl font-semibold transition">
                    Update Profile
                </button>
                <a href="<?= BASE_URL ?>provider/dashboard.php" 
                    class="bg-stone-200 hover:bg-stone-300 text-stone-700 px-8 py-3 rounded-xl font-semibold transition">
                    Back to Dashboard
                </a>
            </div>
        </form>
    </div>

    <!-- Your Services -->
    <div class="mt-8">
        <h2 class="heading text-2xl font-bold text-stone-800 mb-6">Your Services</h2>
        
        <?php if (empty($services)): ?>
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                <div class="text-6xl mb-4">🎪</div>
                <h3 class="font-bold text-xl text-stone-800 mb-2">No services listed yet</h3>
                <p class="text-stone-600 mb-6">Add your wedding services to start receiving bookings!</p>
                <a href="<?= BASE_URL ?>provider/add_service.php" 
                    class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Add First Service
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($services as $service): ?>
                    <div class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-stone-800"><?= htmlspecialchars($service['title']) ?></h3>
                                <p class="text-stone-500 text-sm"><?= ucfirst($service['category']) ?></p>
                            </div>
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
                                echo $icons[$service['category']] ?? '🎪';
                                ?>
                            </span>
                        </div>
                        
                        <p class="text-stone-600 text-sm mb-4">
                            <?= htmlspecialchars(substr($service['description'], 0, 100)) ?>...
                        </p>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xl font-bold text-stone-900">₹<?= number_format($service['price'], 0) ?></p>
                                <p class="text-stone-500 text-xs">per service</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                <?= $service['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $service['is_available'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="<?= BASE_URL ?>provider/add_service.php" 
                    class="inline-block bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold transition">
                    Add New Service
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
