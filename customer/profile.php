<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    setAlert("Please login to access your profile", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// FETCH CURRENT USER DATA
$stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    setAlert("User not found. Please login again.", "error");
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // VALIDATE NAME
    if (empty($name)) {
        $errors[] = "Name cannot be empty";
    }
    
    // VALIDATE PHONE (OPTIONAL BUT IF PROVIDED MUST BE VALID)
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be 10 digits";
    }
    
    // HANDLE PASSWORD CHANGE
    $password_updated = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // VERIFY CURRENT PASSWORD
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_hash = $stmt->fetchColumn();
        
        if (!password_verify($current_password, $current_hash)) {
            $errors[] = "Current password is incorrect";
        } elseif (empty($new_password)) {
            $errors[] = "New password cannot be empty";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } else {
            $password_updated = true;
        }
    }
    
    // UPDATE DATABASE IF NO ERRORS
    if (empty($errors)) {
        try {
            if ($password_updated) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $hashed_password, $_SESSION['user_id']]);
                setAlert("Profile updated successfully! Password changed.", "success");
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $_SESSION['user_id']]);
                setAlert("Profile updated successfully!", "success");
            }
            
            // UPDATE SESSION NAME
            $_SESSION['name'] = $name;
            
            // REFRESH USER DATA
            $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $errors[] = "Database error. Please try again.";
        }
    }
    
    // SHOW ERRORS
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setAlert($error, "error");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .form-card { transition: transform 0.3s; }
        .form-card:hover { transform: translateY(-2px); }
    </style>
    <style>
/* Minimal fallback styles for offline demo */
body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
.btn { background: #e53e3e; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; }
.card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.alert { padding: 12px; border-radius: 4px; margin: 15px 0; }
.alert-error { background: #fee; border-left: 4px solid #c53030; color: #c53030; }
.alert-success { background: #efe; border-left: 4px solid #38a169; color: #38a169; }
</style>
</head>
<body class="bg-stone-50 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-50 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">✨</span>
                    <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700">SAMAAROH</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>customer/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">← Back to Dashboard</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php displayAlert(); ?>

        <div class="text-center mb-10">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">My Profile</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Manage your account details and security settings for your Nadiad wedding planning
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Summary Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                    <div class="w-24 h-24 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-600 text-4xl">
                        👤
                    </div>
                    <h2 class="font-bold text-xl text-stone-800"><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="text-stone-500 mt-1"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-stone-500 mt-1">
                        <?php if (!empty($user['phone'])): ?>
                            <span class="text-green-600">✓</span> Verified Phone
                        <?php else: ?>
                            <span class="text-amber-600">!</span> Add phone for booking updates
                        <?php endif; ?>
                    </p>
                    
                    <div class="mt-6 pt-6 border-t border-stone-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-stone-500">Account Type</span>
                            <span class="font-medium text-stone-800">Customer</span>
                        </div>
                        <div class="flex justify-between text-sm mt-2">
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
                
                <div class="mt-6 bg-amber-50 rounded-xl p-4">
                    <h3 class="font-bold text-amber-800 mb-2">💡 Profile Tips</h3>
                    <ul class="text-amber-700 text-sm space-y-1">
                        <li>• Add phone number to receive booking updates via SMS</li>
                        <li>• Use a strong password for account security</li>
                        <li>• Keep your contact details updated for smooth wedding planning</li>
                    </ul>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="form-card bg-white rounded-2xl border border-stone-200 shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-stone-800 mb-6">Edit Profile</h2>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Name Field -->
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($user['name']) ?>">
                        </div>
                        
                        <!-- Email Field (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                            <input type="email" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-200 bg-stone-50 text-stone-500 cursor-not-allowed"
                                   value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <p class="text-xs text-stone-400 mt-1">Email cannot be changed. Contact support if needed.</p>
                        </div>
                        
                        <!-- Phone Field -->
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Phone Number (Optional)</label>
                            <input type="tel" name="phone" 
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="10-digit mobile number"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                   pattern="[0-9]{10}"
                                   maxlength="10">
                            <p class="text-xs text-stone-400 mt-1">We'll send booking updates and reminders via SMS</p>
                        </div>
                        
                        <!-- Password Section -->
                        <div class="pt-6 border-t border-stone-200">
                            <h3 class="font-bold text-lg text-stone-800 mb-4">Change Password</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Current Password</label>
                                    <input type="password" name="current_password" 
                                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                    <p class="text-xs text-stone-400 mt-1">Leave blank to keep current password</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">New Password</label>
                                    <input type="password" name="new_password" 
                                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Confirm New Password</label>
                                    <input type="password" name="confirm_password" 
                                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="pt-6 border-t border-stone-200 flex gap-4">
                            <button type="submit" 
                                    class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-lg transition">
                                Save Changes
                            </button>
                            <a href="<?= BASE_URL ?>customer/dashboard.php" 
                               class="flex-1 bg-stone-200 hover:bg-stone-300 text-stone-800 font-medium py-3 rounded-xl text-center transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-300 py-10 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex justify-center mb-6">
                <span class="text-4xl">✨</span>
                <h2 class="heading text-2xl font-bold text-white ml-2">SAMAAROH</h2>
            </div>
            <p class="max-w-2xl mx-auto mb-6">
                Nadiad's trusted wedding planning platform. Connecting families with verified vendors since 2026.
            </p>
            <p class="text-stone-500 text-sm">
                &copy; 2026 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.<br>
                BCA Final Year Project by Kishan Marwadi
            </p>
        </div>
    </footer>

</body>
</html>