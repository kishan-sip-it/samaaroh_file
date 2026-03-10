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
            $_SESSION['invitation_link'] = BASE_URL . "accept_admin_invitation.php?token=" . $token;
            setAlert("Invitation created! Share the link below with the new admin.", "success");
        } catch (PDOException $e) {
            error_log("Invitation error: " . $e->getMessage());
            setAlert("Failed to create invitation. Please try again.", "error");
        }
    }
}

// FETCH STATS
$stats = [
    'customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND is_verified = 1")->fetchColumn(),
    'providers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'provider' AND is_verified = 1")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services WHERE is_available = 1")->fetchColumn(),
    'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'pending_invitations' => $pdo->query("SELECT COUNT(*) FROM admin_invitations WHERE accepted_at IS NULL AND expires_at > NOW()")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .invitation-card { transition: transform 0.3s; }
        .invitation-card:hover { transform: translateY(-2px); }
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
<body class="bg-stone-50 min-h-screen">
    <?php include '../includes/navbar.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php displayAlert(); ?>
        
        <!-- Invitation Link (if just created) -->
        <?php if (isset($_SESSION['invitation_link'])): ?>
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-amber-700 font-medium">
                            ✨ Invitation Link (Valid for 7 days):
                        </p>
                        <div class="mt-2 bg-white p-3 rounded-lg font-mono text-xs break-all">
                            <?php echo htmlspecialchars($_SESSION['invitation_link']); ?>
                        </div>
                        <p class="mt-2 text-xs text-amber-600">
                            ⚠️ For demo purposes only. In production, this would be sent via email.
                        </p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['invitation_link']); ?>
        <?php endif; ?>

        <div class="text-center mb-10">
            <h1 class="heading text-4xl font-bold text-stone-800">Admin Dashboard</h1>
            <p class="text-stone-500">Nadiad Wedding Platform Management</p>
        </div>
        
        <!-- Admin Actions -->
        <section class="mb-12">
            <h2 class="text-xl font-bold text-stone-800 mb-6">Admin Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="<?= BASE_URL ?>admin/invite_admin.php" class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center group-hover:bg-amber-200 transition">
                            <span class="text-2xl">👑</span>
                        </div>
                        <h3 class="font-bold text-lg text-stone-800">Invite Admin</h3>
                    </div>
                    <p class="text-stone-600 text-sm">Add new admin members via email invitation</p>
                </a>
                
                <a href="<?= BASE_URL ?>admin/manage_users.php" class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition">
                            <span class="text-2xl">👥</span>
                        </div>
                        <h3 class="font-bold text-lg text-stone-800">Manage Users</h3>
                    </div>
                    <p class="text-stone-600 text-sm">View and manage all customer & provider accounts</p>
                </a>
                
                <a href="<?= BASE_URL ?>admin/view_reports.php" class="bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-lg transition group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center group-hover:bg-rose-200 transition">
                            <span class="text-2xl">📊</span>
                        </div>
                        <h3 class="font-bold text-lg text-stone-800">View Reports</h3>
                    </div>
                    <p class="text-stone-600 text-sm">Monitor user reports and feedback</p>
                </a>
            </div>
        </section>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-rose-600 mb-2"><?= $stats['customers'] ?></div>
                <div class="text-stone-500">Verified Customers</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-amber-600 mb-2"><?= $stats['providers'] ?></div>
                <div class="text-stone-500">Verified Providers</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-green-600 mb-2"><?= $stats['services'] ?></div>
                <div class="text-stone-500">Active Services</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-blue-600 mb-2"><?= $stats['bookings'] ?></div>
                <div class="text-stone-500">Total Bookings</div>
            </div>
        </div>
        
        <!-- Admin Management Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Current Admins -->
            <div class="bg-white rounded-xl p-6 border border-stone-200">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center">
                        <span class="text-rose-600 text-xl">👑</span>
                    </div>
                    <h2 class="font-bold text-xl text-stone-800 ml-3">Current Admins</h2>
                </div>
                
                <div class="space-y-3">
                    <?php
                    $admins = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC")->fetchAll();
                    foreach ($admins as $admin):
                    ?>
                    <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg">
                        <div>
                            <div class="font-medium text-stone-800"><?= htmlspecialchars($admin['name']) ?></div>
                            <div class="text-sm text-stone-500"><?= htmlspecialchars($admin['email']) ?></div>
                        </div>
                        <div class="text-xs text-stone-400">
                            <?= date('M d, Y', strtotime($admin['created_at'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 pt-4 border-t border-stone-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Total Admins:</span>
                        <span class="font-bold text-rose-600"><?= count($admins) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Pending Invitations -->
            <div class="bg-white rounded-xl p-6 border border-stone-200">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <span class="text-amber-600 text-xl">📧</span>
                    </div>
                    <h2 class="font-bold text-xl text-stone-800 ml-3">Pending Invitations</h2>
                </div>
                
                <div class="space-y-3">
                    <?php
                    $pending = $pdo->query("
                        SELECT email, token, expires_at, created_at, u.name as invited_by_name
                        FROM admin_invitations ai
                        LEFT JOIN users u ON ai.invited_by = u.id
                        WHERE ai.accepted_at IS NULL AND ai.expires_at > NOW()
                        ORDER BY ai.created_at DESC
                    ")->fetchAll();
                    
                    if (empty($pending)):
                    ?>
                    <div class="text-center py-6 text-stone-500">
                        <span class="text-2xl">📭</span>
                        <p class="mt-2">No pending invitations</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($pending as $inv): ?>
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                            <div>
                                <div class="font-medium text-stone-800"><?= htmlspecialchars($inv['email']) ?></div>
                                <div class="text-sm text-stone-500">Invited by <?= htmlspecialchars($inv['invited_by_name']) ?></div>
                                <div class="text-xs text-amber-600">Expires: <?= date('M d, Y H:i', strtotime($inv['expires_at'])) ?></div>
                            </div>
                            <div class="text-xs text-stone-400">
                                <?= date('M d', strtotime($inv['created_at'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4 pt-4 border-t border-stone-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Pending:</span>
                        <span class="font-bold text-amber-600"><?= count($pending) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
     </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>