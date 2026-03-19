<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    try {
        // CHECK EMAIL EXISTENCE PROPERLY
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            setAlert("Email already exists!", "error");
        } else {
            // HASH PASSWORD CORRECTLY
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed, $role]);
            
            setAlert("Account created successfully! Please login.", "success");
            header("Location: " . BASE_URL . "login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        setAlert("Registration failed. Please try again.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-stone-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <span class="text-3xl">✨</span>
            <h1 class="text-2xl font-bold text-rose-700 mt-2">SAMAAROH</h1>
            <p class="text-stone-500">Join Nadiad's Wedding Community</p>
        </div>
        
        <?php displayAlert(); ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">I am a...</label>
                <select name="role" required 
                        class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="customer">Customer (Planning a wedding)</option>
                    <option value="provider">Service Provider (Vendor)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
                <input type="text" name="name" required 
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                       placeholder="Enter your name">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                       placeholder="your@email.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                       placeholder="Create a password">
            </div>
            <button type="submit" 
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-3 rounded-lg transition">
                Create Account
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-stone-500">
            <p>Already have an account? 
                <a href="<?= BASE_URL ?>login.php" class="text-rose-600 font-medium hover:underline">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</body>
</html>
