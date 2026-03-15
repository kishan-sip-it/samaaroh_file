<?php
require_once 'config/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate reset token + expiry
                $token = bin2hex(random_bytes(16));
                $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
                $stmt->execute([$token, $expiry, $email]);

                // In real hosting: send email. For local test: show reset link
                $reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "reset-password.php?token=$token";
                $message = "Reset link (valid 1 hour): <a href='$reset_link' target='_blank'>$reset_link</a>";
            } else {
                $message = "No account found with that email.";
            }
        } catch (PDOException $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $message = "System error. Please try again later.";
        }
    } else {
        $message = "Please enter your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Samaaroh</title>
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
.alert-info { background: #e3f2fd; border-left: 4px solid #2196f3; color: #1976d2; }
</style>
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-rose-600 to-amber-500 p-6 text-center">
            <div class="flex justify-center mb-3">
                <span class="text-4xl">🔐</span>
            </div>
            <h1 class="heading text-2xl md:text-3xl font-bold text-white">SAMAAROH</h1>
            <p class="text-amber-100 mt-1 text-sm">Reset Your Password</p>
        </div>
        
        <!-- Forgot Password Form -->
        <div class="p-6 md:p-8">
            <?php if ($message): ?>
                <div class="alert alert-info">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <h2 class="text-xl font-bold text-stone-800 text-center mb-4">Forgot Password?</h2>
            <p class="text-stone-600 text-center text-sm mb-6">
                Enter your email address and we'll send you a link to reset your password.
            </p>
            
            <form method="POST" id="forgot-form" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="your@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <p class="text-xs text-stone-400 mt-1">
                        We'll send a reset link to this email address
                    </p>
                </div>
                
                <button type="submit" 
                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3.5 rounded-xl transition duration-200 shadow-md hover:shadow-lg">
                    Send Reset Link
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="mt-8 pt-6 border-t border-stone-100">
                <p class="text-center text-sm text-stone-600">
                    Remember your password? 
                    <a href="<?= BASE_URL ?>login.php" class="font-medium text-rose-600 hover:text-rose-700">Back to Login</a>
                </p>
                <p class="text-center text-xs text-stone-400 mt-3">
                    © 2026 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.
                </p>
            </div>
        </div>
    </div>

    <script>
    // Auto-focus email field
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('email').focus();
    });
    </script>
</body>
</html>
