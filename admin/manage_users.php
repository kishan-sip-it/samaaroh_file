<?php
require_once '../config/config.php';

// AUTH CHECK: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Admin access required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE USER ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'verify_user' && isset($_POST['user_id'])) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            if ($stmt->execute([$_POST['user_id']])) {
                setAlert("User verified successfully", "success");
            }
        } 
        elseif ($_POST['action'] === 'unverify_user' && isset($_POST['user_id'])) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 0 WHERE id = ?");
            if ($stmt->execute([$_POST['user_id']])) {
                setAlert("User verification removed", "success");
            }
        }
        elseif ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
            // Don't allow deletion of admins
            $user = $pdo->prepare("SELECT role FROM users WHERE id = ?")->execute([$_POST['user_id']]) ? 
                    $pdo->query("SELECT role FROM users WHERE id = " . $_POST['user_id'])->fetch() : null;
            
            if ($user && $user['role'] === 'admin') {
                setAlert("Cannot delete admin users", "error");
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$_POST['user_id']])) {
                    setAlert("User deleted successfully", "success");
                }
            }
        }
        header("Location: " . BASE_URL . "admin/manage_users.php");
        exit();
    }
}

// FETCH USERS WITH FILTERS
$role = $_GET['role'] ?? 'all';
$verification = $_GET['verification'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($role !== 'all') {
    $where_conditions[] = "u.role = ?";
    $params[] = $role;
}

if ($verification !== 'all') {
    $where_conditions[] = "u.is_verified = ?";
    $params[] = $verification === 'verified' ? 1 : 0;
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$query = "
    SELECT u.*, 
           COUNT(DISTINCT b.id) as total_bookings,
           COUNT(DISTINCT CASE WHEN b.status = 'confirmed' THEN b.id END) as confirmed_bookings,
           MAX(b.booking_date) as last_booking_date
    FROM users u
    LEFT JOIN bookings b ON u.id = b.customer_id
    $where_clause
    GROUP BY u.id
    ORDER BY u.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// STATS
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'providers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'provider'")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'verified' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn(),
    'unverified' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 0")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Samaaroh Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <?php include '../includes/navbar.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php displayAlert(); ?>
        
        <div class="text-center mb-10">
            <h1 class="heading text-4xl font-bold text-stone-800">Manage Users</h1>
            <p class="text-stone-500">View and manage all platform users</p>
        </div>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-rose-600 mb-2"><?= $stats['total_users'] ?></div>
                <div class="text-stone-500">Total Users</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-amber-600 mb-2"><?= $stats['verified'] ?></div>
                <div class="text-stone-500">Verified Users</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-stone-600 mb-2"><?= $stats['unverified'] ?></div>
                <div class="text-stone-500">Unverified Users</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl border border-stone-200 p-6 mb-8 shadow-sm">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Name, email, phone..." 
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="all" <?= $role === 'all' ? 'selected' : '' ?>>All Roles</option>
                        <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customers</option>
                        <option value="provider" <?= $role === 'provider' ? 'selected' : '' ?>>Providers</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admins</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Verification</label>
                    <select name="verification" class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="all" <?= $verification === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="verified" <?= $verification === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="unverified" <?= $verification === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        Filter Users
                    </button>
                </div>
            </form>
            <?php if (!empty($search) || $role !== 'all' || $verification !== 'all'): ?>
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-stone-500">
                    Showing <?= count($users) ?> user(s)
                </div>
                <a href="<?= BASE_URL ?>admin/manage_users.php" class="inline-flex items-center px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white font-medium rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear filters
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Users Table -->
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <?php if (empty($users)): ?>
            <div class="text-center py-12 text-stone-500">
                <span class="text-4xl">👥</span>
                <p class="mt-4 text-lg">No users found</p>
                <p class="text-sm mt-2">Try adjusting your filters</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-stone-50 border-b border-stone-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Bookings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-stone-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-stone-900"><?= htmlspecialchars($user['name']) ?></div>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($user['email']) ?></div>
                                    <?php if ($user['phone']): ?>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($user['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 
                                       ($user['role'] === 'provider' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?= $user['is_verified'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= $user['is_verified'] ? 'Verified' : 'Unverified' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-stone-500">
                                <div><?= $user['total_bookings'] ?> total</div>
                                <?php if ($user['confirmed_bookings'] > 0): ?>
                                <div class="text-green-600"><?= $user['confirmed_bookings'] ?> confirmed</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-stone-500">
                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <?php if (!$user['is_verified']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="verify_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                            Verify
                                        </button>
                                    </form>
                                    <?php elseif ($user['is_verified'] && $user['role'] !== 'admin'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="unverify_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                                            Unverify
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Back to Dashboard -->
        <div class="text-center mt-8">
            <a href="<?= BASE_URL ?>admin/dashboard.php" class="inline-flex items-center gap-2 text-stone-600 hover:text-rose-600 transition">
                <span>←</span>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
