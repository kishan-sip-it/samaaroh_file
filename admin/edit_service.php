<?php
require_once '../config/config.php';

// Auth check - admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$service_id = $_GET['id'] ?? null;
if (!$service_id) {
    setAlert("Invalid service ID", "error");
    header("Location: " . BASE_URL . "admin/manage_services.php");
    exit();
}

// Fetch service details
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.email as provider_email
    FROM services s 
    JOIN users u ON s.provider_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found", "error");
    header("Location: " . BASE_URL . "admin/manage_services.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE services 
            SET title = ?, description = ?, price = ?, category = ?, is_available = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$title, $description, $price, $category, $is_available, $service_id]);
        
        if ($result) {
            setAlert("Service updated successfully", "success");
            header("Location: " . BASE_URL . "admin/manage_services.php");
            exit();
        } else {
            setAlert("Failed to update service", "error");
        }
    } catch (PDOException $e) {
        error_log("Update service error: " . $e->getMessage());
        setAlert("Database error occurred", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service | Admin | Samaaroh</title>
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
                    <span class="text-2xl"> </span>
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="heading text-xl font-bold tracking-tight text-rose-700">SAMAAROH ADMIN</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium">Dashboard</a>
                    <a href="<?= BASE_URL ?>admin/reports.php" class="text-stone-600 hover:text-rose-600 font-medium">Reports</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="heading text-3xl font-bold text-stone-800">Edit Service</h1>
            <p class="text-stone-600 mt-2">Update service information for <?= htmlspecialchars($service['provider_name']) ?></p>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Service Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($service['title']) ?>" required
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Category</label>
                        <select name="category" required
                                class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                            <option value="catering" <?= $service['category'] === 'catering' ? 'selected' : '' ?>>Catering</option>
                            <option value="photography" <?= $service['category'] === 'photography' ? 'selected' : '' ?>>Photography</option>
                            <option value="decoration" <?= $service['category'] === 'decoration' ? 'selected' : '' ?>>Decoration</option>
                            <option value="music" <?= $service['category'] === 'music' ? 'selected' : '' ?>>Music</option>
                            <option value="venue" <?= $service['category'] === 'venue' ? 'selected' : '' ?>>Venue</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Price (Rs.)</label>
                        <input type="number" name="price" value="<?= htmlspecialchars($service['price']) ?>" required
                               min="0" step="0.01"
                               class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Status</label>
                        <div class="flex items-center gap-2 mt-3">
                            <input type="checkbox" name="is_available" <?= $service['is_available'] ? 'checked' : '' ?>
                                   class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                            <label for="is_available" class="text-sm text-gray-700">Service Available</label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Description</label>
                    <textarea name="description" rows="4" required
                              class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-rose-500"><?= htmlspecialchars($service['description']) ?></textarea>
                </div>
                
                <!-- Service Info (Read-only) -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-2">Service Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Provider:</span>
                            <span class="font-medium ml-2"><?= htmlspecialchars($service['provider_name']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium ml-2"><?= htmlspecialchars($service['provider_email']) ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Service ID:</span>
                            <span class="font-medium ml-2">#<?= $service['id'] ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium ml-2"><?= date('M d, Y', strtotime($service['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" 
                            class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        Update Service
                    </button>
                    <a href="<?= BASE_URL ?>admin/manage_services.php" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
