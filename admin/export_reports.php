<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$report_type = $_GET['type'] ?? 'users';
$date_range = $_GET['date_range'] ?? 'all';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// CSV Header
switch ($report_type) {
    case 'users':
        fputcsv($output, ['Report Type', 'Total Users', 'Customers', 'Providers', 'Admins', 'Date Range']);
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(created_at) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE 1=1 $date_filter");
        $stmt->execute($params);
        $total_users = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer' $date_filter");
        $stmt->execute($params);
        $customers = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'provider' $date_filter");
        $stmt->execute($params);
        $providers = $stmt->fetch()['total'];
        
        $admins = $total_users - $customers - $providers;
        
        fputcsv($output, ['Users', $total_users, $customers, $providers, $admins, $date_range]);
        break;
        
    case 'bookings':
        fputcsv($output, ['Report Type', 'Total Bookings', 'Pending', 'Confirmed', 'Paid/Completed', 'Date Range']);
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(booking_date) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE 1=1 $date_filter");
        $stmt->execute($params);
        $total_bookings = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending' $date_filter");
        $stmt->execute($params);
        $pending_bookings = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed' $date_filter");
        $stmt->execute($params);
        $confirmed_bookings = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'paid' OR status = 'completed' $date_filter");
        $stmt->execute($params);
        $paid_bookings = $stmt->fetch()['total'];
        
        fputcsv($output, ['Bookings', $total_bookings, $pending_bookings, $confirmed_bookings, $paid_bookings, $date_range]);
        break;
        
    case 'revenue':
        fputcsv($output, ['Report Type', 'Total Revenue', 'Advance Revenue', 'Platform Fees (2%)', 'Provider Earnings', 'Date Range']);
        
        if ($date_range !== 'all') {
            $date_filter = " AND DATE(booking_date) = DATE(:date)";
            $params = [$date_range];
        } else {
            $date_filter = "";
            $params = [];
        }
        
        $stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM bookings WHERE status IN ('paid', 'completed') $date_filter");
        $stmt->execute($params);
        $total_revenue = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT SUM(advance_amount) as total FROM bookings WHERE advance_amount > 0 $date_filter");
        $stmt->execute($params);
        $advance_revenue = $stmt->fetch()['total'] ?? 0;
        
        $platform_fees = $total_revenue * 0.02;
        $provider_earnings = $total_revenue - $platform_fees;
        
        fputcsv($output, ['Revenue', $total_revenue, $advance_revenue, $platform_fees, $provider_earnings, $date_range]);
        break;
}

fclose($output);
exit();
?>
