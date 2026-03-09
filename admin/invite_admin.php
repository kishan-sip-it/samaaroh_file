<?php
require_once '../config/config.php';

// AUTH CHECK: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Admin access required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE INVITATION CREATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite_email'])) {
    $email = trim(strtolower($_POST['invite_email']));
    
    // VALIDATE EMAIL
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setAlert("Invalid email address", "error");
    } 
    // CHECK IF USER ALREADY EXISTS AS ADMIN
    elseif ($pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'")->execute([$email]) && $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'")->fetchColumn()) {
        setAlert("This email is already an admin", "error");
    } 
    // CHECK IF INVITATION ALREADY EXISTS
    elseif ($pdo->prepare("SELECT id FROM admin_invitations WHERE email = ? AND accepted_at IS NULL AND expires_at > NOW()")->execute([$email]) && $pdo->prepare("SELECT id FROM admin_invitations WHERE email = ? AND accepted_at IS NULL AND expires_at > NOW()")->fetchColumn()) {
        setAlert("Invitation already sent to this email (valid for 7 days)", "error");
    } 
    else {
        // GENERATE SECURE TOKEN
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_invitations (email, token, invited_by, expires_at) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$email, $token, $_SESSION['user_id'], $expires]);
            
            // FOR DEMO: Show invitation link (in real app, send via email)
            $_SESSION['invitation_link'] = BASE_URL . "admin/aceept_admin_invitation.php?token=" . $token;
            setAlert("Invitation created! Share the link below with the new admin.", "success");
        } catch (PDOException $e) {
            error_log("Invitation error: " . $e->getMessage());
            setAlert("Failed to create invitation. Please try again.", "error");
        }
    }
    
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Admin | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <?php include '../includes/navbar.php'; ?>
    
    <main class="max-w-4xl mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="heading text-3xl font-bold text-stone-800">Invite New Admin</h1>
            <p class="text-stone-500">Add administrators to help manage Samaaroh platform</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Admin Email Address</label>
                    <input type="email" name="invite_email" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="new.admin@example.com">
                    <p class="text-xs text-stone-400 mt-1">
                        The invitation link will be valid for 7 days and can only be used once
                    </p>
                </div>
                
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                    <h3 class="font-bold text-amber-800 mb-2">📋 How it works:</h3>
                    <ol class="text-sm text-amber-700 space-y-1 list-decimal list-inside">
                        <li>Generate secure invitation link</li>
                        <li>Share link with potential admin</li>
                        <li>They set password and join admin team</li>
                        <li>Link expires after 7 days or first use</li>
                    </ol>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" 
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 rounded-xl transition">
                        Generate Invitation Link
                    </button>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" 
                       class="px-6 py-3 border border-stone-300 rounded-xl font-medium hover:bg-stone-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
