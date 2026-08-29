<?php
require_once 'config/config.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            setAlert("Invalid email or password. Please try again.", "error");
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'customer') {
                header("Location: " . BASE_URL . "customer/dashboard.php");
            } elseif ($user['role'] === 'provider') {
                header("Location: " . BASE_URL . "provider/dashboard.php");
            }elseif ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "index.php");
            }
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        setAlert("Login failed. Please try again later.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <!-- Header -->
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <svg width="60" height="60" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="bg-gradient-to-br from-rose-600 to-amber-500 rounded-2xl p-3 shadow-lg">
                    <circle cx="20" cy="20" r="16" stroke="white" stroke-width="2" fill="none"/>
                    <circle cx="20" cy="20" r="12" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M20 8 L24 16 L20 24 L16 16 Z" fill="white"/>
                    <path d="M20 8 L22 14 L20 16 L18 14 Z" fill="#fbbf24"/>
                </svg>
            </div>
            <h2 class="heading text-3xl font-bold text-stone-800">Welcome Back</h2>
            <p class="text-stone-600 mt-2">Sign in to your Samaaroh account</p>
        </div>

        <!-- Alert -->
        <?php displayAlert(); ?>

        <!-- Login Form -->
        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-stone-700 mb-2">
                    Email Address
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="Enter your email"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-stone-700 mb-2">
                    Password
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="Enter your password"
                >
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-stone-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-stone-700">
                        Remember me
                    </label>
                </div>
                <a href="<?= BASE_URL ?>forgot-password.php" class="text-sm text-rose-600 hover:text-rose-700 transition">
                    Forgot password?
                </a>
            </div>

            <button
                type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-semibold transition transform hover:scale-105"
            >
                Sign In
            </button>
        </form>

        <!-- Register Link -->
        <div class="text-center">
            <p class="text-stone-600">
                Don't have an account? 
                <a href="<?= BASE_URL ?>register.php" class="text-rose-600 hover:text-rose-700 font-semibold transition">
                    Sign up now
                </a>
            </p>
        </div>

        <!-- Demo Accounts -->
        <div class="mt-8 p-4 bg-stone-100 rounded-xl">
            <p class="text-sm text-stone-600 font-medium mb-2">Demo Accounts:</p>
            <div class="text-xs text-stone-500 space-y-1">
                <p><strong>Customer:</strong> customer@example.com / password123</p>
                <p><strong>Provider:</strong> provider@example.com / password123</p>
            </div>
        </div>
    </div>
</body>
</html>
