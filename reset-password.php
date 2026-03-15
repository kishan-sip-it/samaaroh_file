<?php
require_once 'config/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate passwords
    if (empty($password) || strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Use PHP time instead of MySQL NOW() to avoid timezone issues
            $current_time = date('Y-m-d H:i:s');
            
            // Check if token is valid and not expired
            $stmt = $pdo->prepare("SELECT id, email FROM users 
                                 WHERE reset_token = ? AND reset_token_expires > ?");
            $stmt->execute([$token, $current_time]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Hash new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update user password and clear reset token
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);
                
                $success = "Password has been reset successfully! You can now login with your new password.";
                
                // Redirect to login after 3 seconds
                header("refresh:3;url=" . BASE_URL . "login.php");
                
            } else {
                $error = "Invalid or expired reset link. Please request a new password reset.";
            }
            
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            $error = "System error. Please try again later.";
        }
    }
}

// Validate token on page load
if (!empty($token)) {
    try {
        // Debug: Log the token being checked
        error_log("Checking reset token: " . $token);
        
        // Use PHP time instead of MySQL NOW() to avoid timezone issues
        $current_time = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("SELECT id, email, reset_token_expires FROM users 
                             WHERE reset_token = ? AND reset_token_expires > ?");
        $stmt->execute([$token, $current_time]);
        $valid_token = $stmt->fetch();
        
        error_log("Token query result: " . ($valid_token ? 'FOUND' : 'NOT FOUND'));
        error_log("Current PHP time: " . $current_time);
        
        if ($valid_token) {
            error_log("Token expires at: " . $valid_token['reset_token_expires'] . ", Current time: " . $current_time);
        }
        
        if (!$valid_token && empty($error) && empty($success)) {
            // Additional debugging - check if token exists at all
            $stmt = $pdo->prepare("SELECT id, reset_token_expires, reset_token 
                                 FROM users 
                                 WHERE reset_token = ?");
            $stmt->execute([$token]);
            $token_info = $stmt->fetch();
            
            if ($token_info) {
                error_log("Token exists but invalid - Expiry: " . $token_info['reset_token_expires'] . 
                         ", Now: " . $current_time);
                $error = "Token exists but is expired. Please request a new password reset.";
            } else {
                error_log("Token does not exist in database");
                $error = "Invalid reset link. Please request a new password reset.";
            }
        }
    } catch (PDOException $e) {
        error_log("Reset password error: " . $e->getMessage());
        $error = "System error. Please try again later.";
    }
} else {
    $error = "No reset token provided. Please use the link from your email.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Samaaroh</title>
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
                <span class="text-4xl">🔒</span>
            </div>
            <h1 class="heading text-2xl md:text-3xl font-bold text-white">SAMAAROH</h1>
            <p class="text-amber-100 mt-1 text-sm">Set New Password</p>
        </div>
        
        <!-- Reset Password Form -->
        <div class="p-6 md:p-8">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong><br>
                    <?= htmlspecialchars($success) ?>
                </div>
                <div class="text-center mt-6">
                    <a href="<?= BASE_URL ?>login.php" class="inline-block bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 px-6 rounded-xl transition duration-200">
                        Go to Login
                    </a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <strong>Error:</strong><br>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($error)): ?>
                    <h2 class="text-xl font-bold text-stone-800 text-center mb-4">Reset Your Password</h2>
                    <p class="text-stone-600 text-center text-sm mb-6">
                        Enter your new password below.
                    </p>
                    
                    <form method="POST" id="reset-form" class="space-y-5">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">New Password</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="Enter new password (min. 8 characters)">
                            <p class="text-xs text-stone-400 mt-1">
                                Must be at least 8 characters long
                            </p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-1.5">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="Confirm new password">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3.5 rounded-xl transition duration-200 shadow-md hover:shadow-lg">
                            Reset Password
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <a href="<?= BASE_URL ?>login.php" class="text-sm text-stone-600 hover:text-rose-600">
                            Back to Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center mt-6">
                        <a href="<?= BASE_URL ?>forgot-password.php" class="inline-block bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 px-6 rounded-xl transition duration-200">
                            Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="mt-8 pt-6 border-t border-stone-100 text-center">
                <p class="text-xs text-stone-400">
                    © 2026 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.
                </p>
            </div>
        </div>
    </div>

    <?php if (empty($error) && empty($success)): ?>
    <script>
    // Password confirmation validation
    document.getElementById('reset-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match. Please make sure both passwords are the same.');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
    });
    
    // Auto-focus password field
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('password').focus();
    });
    </script>
    <?php endif; ?>
</body>
</html>
