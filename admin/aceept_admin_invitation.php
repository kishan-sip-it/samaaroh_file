<?php
require_once 'config/config.php';

// GET TOKEN FROM URL
$token = $_GET['token'] ?? null;
if (!$token) {
    setAlert("Invalid invitation link", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// VALIDATE TOKEN
$stmt = $pdo->prepare("
    SELECT id, email, token, expires_at, accepted_at 
    FROM admin_invitations 
    WHERE token = ? 
    LIMIT 1
");
$stmt->execute([$token]);
$invitation = $stmt->fetch();

// CHECK IF VALID
if (!$invitation) {
    setAlert("Invalid or expired invitation", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($invitation['accepted_at']) {
    setAlert("This invitation has already been used", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if (strtotime($invitation['expires_at']) < time()) {
    setAlert("This invitation has expired (7-day limit)", "error");
    // Soft delete expired invitation
    $pdo->prepare("DELETE FROM admin_invitations WHERE id = ?")->execute([$invitation['id']]);
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE PASSWORD SETTING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm) $errors[] = "Passwords do not match";
    
    if (empty($errors)) {
        try {
            // CHECK IF EMAIL ALREADY EXISTS (as non-admin)
            $check = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
            $check->execute([$invitation['email']]);
            $existing = $check->fetch();
            
            if ($existing) {
                // If exists as customer/provider, upgrade to admin
                if ($existing['role'] !== 'admin') {
                    $update = $pdo->prepare("UPDATE users SET role = 'admin', password = ? WHERE id = ?");
                    $update->execute([password_hash($password, PASSWORD_DEFAULT), $existing['id']]);
                } else {
                    $errors[] = "This email is already an admin";
                }
            } else {
                // Create new admin account
                $insert = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, is_verified) 
                    VALUES (?, ?, ?, 'admin', 1)
                ");
                $insert->execute([
                    'Admin ' . ucfirst(explode('@', $invitation['email'])[0]),
                    $invitation['email'],
                    password_hash($password, PASSWORD_DEFAULT)
                ]);
            }
            
            if (empty($errors)) {
                // MARK INVITATION AS ACCEPTED
                $pdo->prepare("UPDATE admin_invitations SET accepted_at = NOW() WHERE id = ?")
                    ->execute([$invitation['id']]);
                
                setAlert("✅ Admin account created successfully! You can now login.", "success");
                header("Location: " . BASE_URL . "login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Admin creation error: " . $e->getMessage());
            $errors[] = "Database error. Please try again.";
        }
    }
    
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
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Admin Invitation | Samaaroh</title>
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-3">
                <span class="text-4xl">👑</span>
            </div>
            <h1 class="heading text-2xl font-bold text-rose-700">Admin Invitation</h1>
            <p class="text-stone-500 mt-1">Set your password to join Samaaroh's admin team</p>
        </div>
        
        <?php displayAlert(); ?>
        
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
            <p class="text-sm text-amber-800">
                ✨ You've been invited to become an admin for Samaaroh — Nadiad's wedding planning platform.
            </p>
            <p class="text-xs text-amber-700 mt-1">
                This invitation expires in <?= ceil((strtotime($invitation['expires_at']) - time()) / 86400) ?> days
            </p>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
                <input type="email" 
                       value="<?= htmlspecialchars($invitation['email']) ?>" 
                       disabled
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 bg-stone-50 text-stone-500 cursor-not-allowed">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Create Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500"
                       placeholder="Minimum 6 characters">
                <p class="text-xs text-stone-400 mt-1">Use a strong password for platform security</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500">
            </div>
            
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 rounded-xl transition">
                Create Admin Account
            </button>
        </form>
        
        <div class="mt-6 pt-4 border-t border-stone-100 text-center">
            <p class="text-xs text-stone-400">
                After creating your account, login at:<br>
                <span class="font-mono bg-stone-100 px-2 py-1 rounded"><?= BASE_URL ?>login.php</span>
            </p>
        </div>
    </div>
</body>
</html>