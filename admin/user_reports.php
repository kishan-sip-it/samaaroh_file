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
    require_once '../vendor/autoload.php'; // Make sure to install TCPDF via composer
    
    $pdf = new TCPDF();
    $pdf->AddPage();
    
    // Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Samaaroh User Report', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Filter: ' . ucfirst($filter) . ' Users', 0, 1, 'C');
    $pdf->Cell(0, 8, 'Generated: ' . date('d M, Y H:i'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Table Header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(15, 8, 'ID', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Name', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Email', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Phone', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Role', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Verified', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Bookings', 1, 1, 'C');
    
    // Table Data
    $pdf->SetFont('helvetica', '', 9);
    foreach ($users as $user) {
        $pdf->Cell(15, 8, $user['id'], 1, 0, 'C');
        $pdf->Cell(40, 8, substr($user['name'], 0, 25), 1, 0, 'L');
        $pdf->Cell(50, 8, substr($user['email'], 0, 35), 1, 0, 'L');
        $pdf->Cell(30, 8, $user['phone'] ?: 'N/A', 1, 0, 'C');
        $pdf->Cell(20, 8, ucfirst($user['role']), 1, 0, 'C');
        $pdf->Cell(20, 8, $user['is_verified'] ? 'Yes' : 'No', 1, 0, 'C');
        $pdf->Cell(25, 8, $user['total_bookings'], 1, 1, 'C');
    }
    
    // Footer
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'Total Users: ' . count($users), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Samaaroh Wedding Platform - Nadiad', 0, 1, 'C');
    
    // Output
    $filename = 'samaaroh_users_' . $filter . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D');
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
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
