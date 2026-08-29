<?php
require_once 'config/config.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        setAlert("Please fill in all required fields.", "error");
    } elseif ($password !== $confirm_password) {
        setAlert("Passwords do not match.", "error");
    } elseif (strlen($password) < 6) {
        setAlert("Password must be at least 6 characters long.", "error");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setAlert("Please enter a valid email address.", "error");
    } else {
        try {
            // Check if email already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                setAlert("Email already exists!", "error");
            } else {
                // Hash password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, role, password, is_verified, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $email, $phone, $role, $hashed, $role === 'provider' ? 0 : 1]);
                
                setAlert("Registration successful! Please login to continue.", "success");
                header("Location: " . BASE_URL . "login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            setAlert("Registration failed. Please try again later.", "error");
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
    <title>Register | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <svg width="60" height="60" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="bg-gradient-to-br from-rose-600 to-amber-500 rounded-2xl p-3 shadow-lg">
                    <circle cx="20" cy="20" r="16" stroke="white" stroke-width="2" fill="none"/>
                    <circle cx="20" cy="20" r="12" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M20 8 L24 16 L20 24 L16 16 Z" fill="white"/>
                    <path d="M20 8 L22 14 L20 16 L18 14 Z" fill="#fbbf24"/>
                </svg>
            </div>
            <h2 class="heading text-3xl font-bold text-stone-800">Create Account</h2>
            <p class="text-stone-600 mt-2">Join Samaaroh wedding planning platform</p>
        </div>

        <!-- Alert -->
        <?php displayAlert(); ?>

        <!-- Registration Form -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
            <form method="POST" class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-2">
                        Full Name *
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-2">
                        Email Address *
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-stone-700 mb-2">
                        Phone Number *
                    </label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        required
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="Enter your phone number"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    >
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">
                        I want to *
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative">
                            <input
                                type="radio"
                                name="role"
                                value="customer"
                                class="peer sr-only"
                                <?= ($_POST['role'] ?? '') === 'customer' ? 'checked' : '' ?>
                            >
                            <div class="p-4 border-2 rounded-xl cursor-pointer transition peer-checked:border-rose-500 peer-checked:bg-rose-50 hover:bg-stone-50">
                                <div class="text-center">
                                    <span class="text-2xl">👰</span>
                                    <p class="font-medium text-stone-800">Plan Wedding</p>
                                    <p class="text-xs text-stone-500">I'm getting married</p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input
                                type="radio"
                                name="role"
                                value="provider"
                                class="peer sr-only"
                                <?= ($_POST['role'] ?? '') === 'provider' ? 'checked' : '' ?>
                            >
                            <div class="p-4 border-2 rounded-xl cursor-pointer transition peer-checked:border-rose-500 peer-checked:bg-rose-50 hover:bg-stone-50">
                                <div class="text-center">
                                    <span class="text-2xl">🎪</span>
                                    <p class="font-medium text-stone-800">Offer Services</p>
                                    <p class="text-xs text-stone-500">I'm a vendor</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700 mb-2">
                        Password *
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="Create a password (min 6 characters)"
                    >
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-2">
                        Confirm Password *
                    </label>
                    <input
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                        placeholder="Confirm your password"
                    >
                </div>

                <!-- Terms Checkbox -->
                <div class="flex items-center">
                    <input
                        id="terms"
                        name="terms"
                        type="checkbox"
                        required
                        class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-stone-300 rounded"
                    >
                    <label for="terms" class="ml-2 block text-sm text-stone-700">
                        I agree to the 
                        <a href="<?= BASE_URL ?>terms.php" class="text-rose-600 hover:text-rose-700 transition">Terms of Service</a> 
                        and 
                        <a href="<?= BASE_URL ?>privacy.php" class="text-rose-600 hover:text-rose-700 transition">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-semibold transition transform hover:scale-105"
                >
                    Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-stone-600">
                    Already have an account? 
                    <a href="<?= BASE_URL ?>login.php" class="text-rose-600 hover:text-rose-700 font-semibold transition">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
