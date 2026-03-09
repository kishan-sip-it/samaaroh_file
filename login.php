<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // CRITICAL: Trim spaces
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // SECURITY: Generic error message (don't reveal if email exists)
        if (!$user || !password_verify($password, $user['password'])) {
            setAlert("Invalid email or password. Please try again.", "error");
            header("Location: " . BASE_URL . "login.php");
            exit();
        }
        
        // VALID LOGIN
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        // REDIRECT BY ROLE
        switch ($user['role']) {
            case 'admin':
                header("Location: " . BASE_URL . "admin/dashboard.php");
                break;
            case 'provider':
                header("Location: " . BASE_URL . "provider/dashboard.php");
                break;
            default:
                header("Location: " . BASE_URL . "customer/dashboard.php");
        }
        exit();
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        setAlert("System error. Please try again later.", "error");
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
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
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-rose-600 to-amber-500 p-6 text-center">
            <div class="flex justify-center mb-3">
                <span class="text-4xl">✨</span>
            </div>
            <h1 class="heading text-2xl md:text-3xl font-bold text-white">SAMAAROH</h1>
            <p class="text-amber-100 mt-1 text-sm">Nadiad's Trusted Wedding Planning Platform</p>
        </div>
        
        <!-- Login Form -->
        <div class="p-6 md:p-8">
            <?php displayAlert(); ?>
            
            <h2 class="text-xl font-bold text-stone-800 text-center mb-6">Welcome Back</h2>
            
            <form method="POST" id="login-form" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="your@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="••••••••">
                    <p class="text-xs text-stone-400 mt-1 text-right">
                        <a href="<?= BASE_URL ?>forgot-password.php" class="text-rose-600 hover:underline">Forgot password?</a>
                    </p>
                </div>
                
                <button type="submit" 
                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3.5 rounded-xl transition duration-200 shadow-md hover:shadow-lg">
                    Sign In to Your Account
                </button>
            </form>
            
            <!-- Register Links -->
            <div class="mt-8 pt-6 border-t border-stone-100">
                <p class="text-center text-sm text-stone-600">
                    Don't have an account? 
                    <a href="<?= BASE_URL ?>register.php" class="font-medium text-rose-600 hover:text-rose-700">Register now</a>
                </p>
                <p class="text-center text-xs text-stone-400 mt-3">
                    © 2024 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.
                </p>
            </div>
        </div>
    </div>

    <script>
    // Client-side password trimming (prevents space issues)
    document.getElementById('login-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password');
        password.value = password.value.trim();
    });
    
    // Auto-focus email field
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('email').focus();
    });
    </script>
</body>
</html>