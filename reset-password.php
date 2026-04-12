<?php
require_once 'config/config.php';

$message = "";
$token = $_GET['token'] ?? '';

if (empty($token)) {
    setAlert("Invalid reset link.", "error");
    header("Location: " . BASE_URL . "forgot-password.php");
    exit();
}

// Verify token
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    setAlert("Invalid or expired reset link.", "error");
    header("Location: " . BASE_URL . "forgot-password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($password) || empty($confirm_password)) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                      <strong>Error!</strong> Please fill in all fields.
                    </div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                      <strong>Error!</strong> Passwords do not match.
                    </div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                      <strong>Error!</strong> Password must be at least 6 characters long.
                    </div>";
    } else {
        try {
            // Update password and clear token
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            
            setAlert("Password reset successful! Please login with your new password.", "success");
            header("Location: " . BASE_URL . "login.php");
            exit();
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                          <strong>Error!</strong> System error. Please try again later.
                        </div>";
        }
    }
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
            <h2 class="heading text-3xl font-bold text-stone-800">Reset Password</h2>
            <p class="text-stone-600 mt-2">Enter your new password below.</p>
        </div>

        <!-- Alert -->
        <?php echo $message; ?>

        <!-- Reset Password Form -->
        <form method="POST" class="space-y-6">
            <div>
                <label for="password" class="block text-sm font-medium text-stone-700 mb-2">
                    New Password
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    minlength="6"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="Enter new password (min 6 characters)"
                >
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-stone-700 mb-2">
                    Confirm New Password
                </label>
                <input
                    id="confirm_password"
                    name="confirm_password"
                    type="password"
                    required
                    minlength="6"
                    class="w-full px-4 py-3 border border-stone-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    placeholder="Confirm new password"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-semibold transition transform hover:scale-105"
            >
                Reset Password
            </button>
        </form>

        <!-- Back to Login -->
        <div class="text-center">
            <a href="<?= BASE_URL ?>login.php" class="text-rose-600 hover:text-rose-700 font-semibold transition">
                ← Back to Login
            </a>
        </div>
    </div>
</body>
</html>
