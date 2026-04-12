<?php
require_once 'config/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                      <strong>Error!</strong> Please enter your email address.
                    </div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                      <strong>Error!</strong> Please enter a valid email address.
                    </div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
                $stmt->execute([$token, $expiry, $email]);
                
                // In a real application, you would send an email here
                // For demo, we'll show the reset link
                $reset_link = BASE_URL . "reset-password.php?token=" . $token;
                
                $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded'>
                              <strong>Success!</strong> Password reset link has been sent to your email.
                              <br><small>For demo: <a href='$reset_link' class='underline'>$reset_link</a></small>
                            </div>";
            } else {
                // Don't reveal if email exists or not
                $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded'>
                              <strong>Success!</strong> If an account exists with this email, a password reset link has been sent.
                            </div>";
            }
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
    <title>Forgot Password | Samaaroh</title>
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
            <h2 class="heading text-3xl font-bold text-stone-800">Forgot Password?</h2>
            <p class="text-stone-600 mt-2">No worries, we'll send you reset instructions.</p>
        </div>

        <!-- Alert -->
        <?php echo $message; ?>

        <!-- Forgot Password Form -->
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

            <button
                type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-semibold transition transform hover:scale-105"
            >
                Send Reset Link
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
