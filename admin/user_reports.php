<?php
require_once '../config/config.php';

// AUTH CHECK: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Admin access required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE EXPORT REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_type'])) {
    $export_type = $_POST['export_type'];
    $user_filter = $_POST['user_filter'] ?? 'all';
    
    // Build query based on filter
    $where_clause = "";
    $params = [];
    
    if ($user_filter === 'customers') {
        $where_clause = "WHERE role = 'customer'";
    } elseif ($user_filter === 'providers') {
        $where_clause = "WHERE role = 'provider'";
    }
    
    // Get user data
    $query = "
        SELECT u.id, u.name, u.email, u.phone, u.role, u.is_verified, u.created_at,
               COUNT(DISTINCT b.id) as total_bookings,
               COUNT(DISTINCT CASE WHEN b.status = 'confirmed' THEN b.id END) as confirmed_bookings,
               COALESCE(SUM(CASE WHEN b.status = 'confirmed' THEN b.total_price END), 0) as total_revenue
        FROM users u
        LEFT JOIN bookings b ON u.id = b.customer_id
        $where_clause
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    if ($export_type === 'pdf') {
        generatePDFReport($users, $user_filter);
    } elseif ($export_type === 'excel') {
        generateExcelReport($users, $user_filter);
    }
    exit();
}

// GET STATS FOR DASHBOARD
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'providers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'provider'")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'verified' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn(),
    'unverified' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 0")->fetchColumn(),
];

// PDF GENERATION FUNCTION
function generatePDFReport($users, $filter) {
    // Generate a text-based report that can be saved as PDF
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment;filename="samaaroh_users_' . $filter . '_' . date('Y-m-d') . '.txt"');
    header('Cache-Control: max-age=0');
    
    $content = "SAMAAROH USER REPORT\n";
    $content .= "===================\n\n";
    $content .= "Filter: " . ucfirst($filter) . " Users\n";
    $content .= "Generated: " . date('d M, Y H:i:s') . "\n";
    $content .= "Platform: Samaaroh Wedding Platform\n\n";
    
    $content .= str_repeat("-", 120) . "\n";
    $content .= sprintf("%-5s %-25s %-30s %-15s %-10s %-8s %-12s %-8s %-12s %-12s\n", 
        "ID", "NAME", "EMAIL", "PHONE", "ROLE", "VERIFIED", "JOIN DATE", "BOOKINGS", "CONFIRMED", "REVENUE");
    $content .= str_repeat("-", 120) . "\n";
    
    foreach ($users as $user) {
        $content .= sprintf("%-5d %-25s %-30s %-15s %-10s %-8s %-12s %-8d %-12d Rs.%-10s\n",
            $user['id'],
            substr($user['name'], 0, 24),
            substr($user['email'], 0, 29),
            substr($user['phone'] ?: 'N/A', 0, 14),
            ucfirst($user['role']),
            $user['is_verified'] ? 'Yes' : 'No',
            date('d M Y', strtotime($user['created_at'])),
            $user['total_bookings'],
            $user['confirmed_bookings'],
            number_format($user['total_revenue'], 0)
        );
    }
    
    $content .= str_repeat("-", 120) . "\n\n";
    
    $totalBookings = array_sum(array_column($users, 'total_bookings'));
    $confirmedBookings = array_sum(array_column($users, 'confirmed_bookings'));
    $totalRevenue = array_sum(array_column($users, 'total_revenue'));
    
    $content .= "SUMMARY STATISTICS\n";
    $content .= "==================\n\n";
    $content .= "Total Users: " . count($users) . "\n";
    $content .= "Total Bookings: " . $totalBookings . "\n";
    $content .= "Confirmed Bookings: " . $confirmedBookings . "\n";
    $content .= "Total Revenue: Rs." . number_format($totalRevenue, 0) . "\n";
    $content .= "Average Revenue per User: Rs." . number_format($totalRevenue / max(count($users), 1), 0) . "\n\n";
    
    $content .= "REPORT DETAILS\n";
    $content .= "=============\n\n";
    $content .= "This report contains user data from the Samaaroh wedding platform.\n";
    $content .= "Users are filtered by: " . $filter . "\n";
    $content .= "Revenue calculations include only confirmed bookings.\n";
    $content .= "For questions, contact the platform administrator.\n\n";
    
    $content .= "Generated on: " . date('d M, Y H:i:s') . "\n";
    $content .= "Report ID: " . uniqid('REPORT_') . "\n";
    
    echo $content;
}

// EXCEL GENERATION FUNCTION
function generateExcelReport($users, $filter) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="samaaroh_users_' . $filter . '_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Start Excel output
    echo "<table border='1'>";
    
    // Header
    echo "<tr>
        <th><b>ID</b></th>
        <th><b>Name</b></th>
        <th><b>Email</b></th>
        <th><b>Phone</b></th>
        <th><b>Role</b></th>
        <th><b>Verified</b></th>
        <th><b>Join Date</b></th>
        <th><b>Total Bookings</b></th>
        <th><b>Confirmed Bookings</b></th>
        <th><b>Total Revenue</b></th>
    </tr>";
    
    // Data
    foreach ($users as $user) {
        echo "<tr>
            <td>{$user['id']}</td>
            <td>" . htmlspecialchars($user['name']) . "</td>
            <td>" . htmlspecialchars($user['email']) . "</td>
            <td>" . htmlspecialchars($user['phone']) . "</td>
            <td>" . ucfirst($user['role']) . "</td>
            <td>" . ($user['is_verified'] ? 'Yes' : 'No') . "</td>
            <td>" . date('d M, Y', strtotime($user['created_at'])) . "</td>
            <td>{$user['total_bookings']}</td>
            <td>{$user['confirmed_bookings']}</td>
            <td>₹" . number_format($user['total_revenue'], 2) . "</td>
        </tr>";
    }
    
    // Summary
    echo "<tr>
        <td colspan='6'><b>TOTAL</b></td>
        <td><b>" . array_sum(array_column($users, 'total_bookings')) . "</b></td>
        <td><b>" . array_sum(array_column($users, 'confirmed_bookings')) . "</b></td>
        <td><b>₹" . number_format(array_sum(array_column($users, 'total_revenue')), 2) . "</b></td>
    </tr>";
    
    echo "</table>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reports | Samaaroh Admin</title>
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
            <h1 class="heading text-4xl font-bold text-stone-800">User Reports</h1>
            <p class="text-stone-500">Generate and download user analytics reports</p>
        </div>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-rose-600 mb-2"><?= $stats['total_users'] ?></div>
                <div class="text-stone-500">Total Users</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-amber-600 mb-2"><?= $stats['customers'] ?></div>
                <div class="text-stone-500">Customers</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-green-600 mb-2"><?= $stats['providers'] ?></div>
                <div class="text-stone-500">Providers</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-blue-600 mb-2"><?= $stats['verified'] ?></div>
                <div class="text-stone-500">Verified Users</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-purple-600 mb-2"><?= $stats['unverified'] ?></div>
                <div class="text-stone-500">Unverified Users</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-stone-600 mb-2"><?= $stats['admins'] ?></div>
                <div class="text-stone-500">Admins</div>
            </div>
        </div>
        
        <!-- Export Forms -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- All Users Report -->
            <div class="bg-white rounded-xl p-6 border border-stone-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">👥</span>
                    </div>
                    <h2 class="font-bold text-xl text-stone-800 ml-3">All Users</h2>
                </div>
                <p class="text-stone-600 mb-6">Complete list of all platform users including customers, providers, and admins.</p>
                
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="user_filter" value="all">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="export_type" value="pdf" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Text Report
                        </button>
                        <button type="submit" name="export_type" value="excel" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2l-4-4m0 0l-4 4m4-4v12"/>
                            </svg>
                            Excel
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Customers Only Report -->
            <div class="bg-white rounded-xl p-6 border border-stone-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">👤</span>
                    </div>
                    <h2 class="font-bold text-xl text-stone-800 ml-3">Customers Only</h2>
                </div>
                <p class="text-stone-600 mb-6">List of all customer accounts with their booking history and revenue data.</p>
                
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="user_filter" value="customers">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="export_type" value="pdf" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Text Report
                        </button>
                        <button type="submit" name="export_type" value="excel" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2l-4-4m0 0l-4 4m4-4v12"/>
                            </svg>
                            Excel
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Providers Only Report -->
            <div class="bg-white rounded-xl p-6 border border-stone-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🎭</span>
                    </div>
                    <h2 class="font-bold text-xl text-stone-800 ml-3">Providers Only</h2>
                </div>
                <p class="text-stone-600 mb-6">List of all service providers with their business details and performance metrics.</p>
                
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="user_filter" value="providers">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="export_type" value="pdf" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Text Report
                        </button>
                        <button type="submit" name="export_type" value="excel" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2l-4-4m0 0l-4 4m4-4v12"/>
                            </svg>
                            Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Report Features -->
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-8">
            <h3 class="font-bold text-lg text-blue-800 mb-4">📊 Report Features</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">📄 PDF Reports</h4>
                    <ul class="space-y-1 text-stone-700 text-sm">
                        <li>• Professional formatted documents</li>
                        <li>• Print-ready layouts</li>
                        <li>• Company branding included</li>
                        <li>• Summary statistics</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">📊 Excel Reports</h4>
                    <ul class="space-y-1 text-stone-700 text-sm">
                        <li>• Data analysis ready</li>
                        <li>• Sortable and filterable</li>
                        <li>• Revenue calculations</li>
                        <li>• Booking statistics</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-blue-200">
                <h4 class="font-semibold text-blue-700 mb-2">📈 Data Included</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-stone-700">
                    <div>
                        <strong>User Information:</strong>
                        <ul class="mt-1 space-y-1">
                            <li>• Name and contact details</li>
                            <li>• Account verification status</li>
                            <li>• Registration date</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Booking Data:</strong>
                        <ul class="mt-1 space-y-1">
                            <li>• Total booking count</li>
                            <li>• Confirmed bookings</li>
                            <li>• Booking success rate</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Financial Metrics:</strong>
                        <ul class="mt-1 space-y-1">
                            <li>• Total revenue per user</li>
                            <li>• Revenue by user type</li>
                            <li>• Platform earnings</li>
                        </ul>
                    </div>
                </div>
            </div>
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
