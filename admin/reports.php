<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get report type
$report_type = $_GET['type'] ?? 'users';
$date_range = $_GET['date_range'] ?? 'all';

// Initialize data
$report_data = [];
$report_title = '';

switch ($report_type) {
    case 'users':
        $report_title = 'User Reports';
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(created_at) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        // Total users
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE 1=1 $date_filter");
        $stmt->execute($params);
        $total_users = $stmt->fetch()['total'];
        
        // Customers only
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer' $date_filter");
        $stmt->execute($params);
        $customers = $stmt->fetch()['total'];
        
        // Providers only
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'provider' $date_filter");
        $stmt->execute($params);
        $providers = $stmt->fetch()['total'];
        
        $report_data = [
            'total_users' => $total_users,
            'customers' => $customers,
            'providers' => $providers,
            'admins' => $total_users - $customers - $providers
        ];
        break;
        
    case 'bookings':
        $report_title = 'Booking Reports';
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(booking_date) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        // Total bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE 1=1 $date_filter");
        $stmt->execute($params);
        $total_bookings = $stmt->fetch()['total'];
        
        // Pending bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending' $date_filter");
        $stmt->execute($params);
        $pending_bookings = $stmt->fetch()['total'];
        
        // Confirmed bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed' $date_filter");
        $stmt->execute($params);
        $confirmed_bookings = $stmt->fetch()['total'];
        
        // Paid bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'paid' OR status = 'completed' $date_filter");
        $stmt->execute($params);
        $paid_bookings = $stmt->fetch()['total'];
        
        $report_data = [
            'total_bookings' => $total_bookings,
            'pending' => $pending_bookings,
            'confirmed' => $confirmed_bookings,
            'paid' => $paid_bookings
        ];
        break;
        
    case 'revenue':
        $report_title = 'Revenue Reports';
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(booking_date) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        // Total revenue
        $stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM bookings WHERE status IN ('paid', 'completed') $date_filter");
        $stmt->execute($params);
        $total_revenue = $stmt->fetch()['total'] ?? 0;
        
        // Advance payments
        $stmt = $pdo->prepare("SELECT SUM(advance_amount) as total FROM bookings WHERE advance_amount > 0 $date_filter");
        $stmt->execute($params);
        $advance_revenue = $stmt->fetch()['total'] ?? 0;
        
        // Platform fees (2% commission)
        $platform_fees = $total_revenue * 0.02;
        
        $report_data = [
            'total_revenue' => $total_revenue,
            'advance_revenue' => $advance_revenue,
            'platform_fees' => $platform_fees,
            'provider_earnings' => $total_revenue - $platform_fees
        ];
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $report_title ?> | Admin | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🎊</span>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="heading text-xl font-bold tracking-tight text-rose-700">SAMAAROH ADMIN</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium">Dashboard</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="heading text-3xl font-bold text-stone-800"><?= $report_title ?></h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Report Type</label>
                    <select id="reportType" onchange="changeReport()" 
                            class="px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="users" <?= $report_type === 'users' ? 'selected' : '' ?>>Users</option>
                        <option value="bookings" <?= $report_type === 'bookings' ? 'selected' : '' ?>>Bookings</option>
                        <option value="revenue" <?= $report_type === 'revenue' ? 'selected' : '' ?>>Revenue</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Date Range</label>
                    <select id="dateRange" onchange="changeReport()" 
                            class="px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="all" <?= $date_range === 'all' ? 'selected' : '' ?>>All Time</option>
                        <option value="<?= date('Y-m-d') ?>" <?= $date_range === date('Y-m-d') ? 'selected' : '' ?>>Today</option>
                        <option value="<?= date('Y-m-d', strtotime('-7 days')) ?>" <?= $date_range === date('Y-m-d', strtotime('-7 days')) ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="<?= date('Y-m-d', strtotime('-30 days')) ?>" <?= $date_range === date('Y-m-d', strtotime('-30 days')) ? 'selected' : '' ?>>Last 30 Days</option>
                    </select>
                </div>
                
                <button onclick="exportReport()" 
                        class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    Export CSV
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if ($report_type === 'users'): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-rose-600"><?= number_format($report_data['total_users']) ?></div>
                    <div class="text-stone-500 text-sm">Total Users</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-blue-600"><?= number_format($report_data['customers']) ?></div>
                    <div class="text-stone-500 text-sm">Customers</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-green-600"><?= number_format($report_data['providers']) ?></div>
                    <div class="text-stone-500 text-sm">Providers</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-purple-600"><?= number_format($report_data['admins']) ?></div>
                    <div class="text-stone-500 text-sm">Admins</div>
                </div>
            <?php endif; ?>
            
            <?php if ($report_type === 'bookings'): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-rose-600"><?= number_format($report_data['total_bookings']) ?></div>
                    <div class="text-stone-500 text-sm">Total Bookings</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-amber-600"><?= number_format($report_data['pending']) ?></div>
                    <div class="text-stone-500 text-sm">Pending</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-blue-600"><?= number_format($report_data['confirmed']) ?></div>
                    <div class="text-stone-500 text-sm">Confirmed</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-green-600"><?= number_format($report_data['paid']) ?></div>
                    <div class="text-stone-500 text-sm">Paid/Completed</div>
                </div>
            <?php endif; ?>
            
            <?php if ($report_type === 'revenue'): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-rose-600">₹<?= number_format($report_data['total_revenue'], 0) ?></div>
                    <div class="text-stone-500 text-sm">Total Revenue</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-blue-600">₹<?= number_format($report_data['advance_revenue'], 0) ?></div>
                    <div class="text-stone-500 text-sm">Advance Payments</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-purple-600">₹<?= number_format($report_data['platform_fees'], 0) ?></div>
                    <div class="text-stone-500 text-sm">Platform Fees (2%)</div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="text-3xl font-bold text-green-600">₹<?= number_format($report_data['provider_earnings'], 0) ?></div>
                    <div class="text-stone-500 text-sm">Provider Earnings</div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function changeReport() {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            window.location.href = `<?= BASE_URL ?>admin/reports.php?type=${reportType}&date_range=${dateRange}`;
        }
        
        function exportReport() {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            window.location.href = `<?= BASE_URL ?>admin/export_reports.php?type=${reportType}&date_range=${dateRange}`;
        }
    </script>
</body>
</html>
