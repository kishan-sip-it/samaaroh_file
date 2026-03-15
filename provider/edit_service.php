<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login as a service provider to edit services", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// GET SERVICE ID FROM URL
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($service_id <= 0) {
    setAlert("Invalid service ID", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// FETCH SERVICE (ENSURE IT BELONGS TO CURRENT PROVIDER)
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE id = ? AND provider_id = ?
");
$stmt->execute([$service_id, $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found or access denied", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $tier = $_POST['tier'];
    $price = floatval($_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // VALIDATION
    $errors = [];
    if (empty($title)) $errors[] = "Service title is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($tier)) $errors[] = "Tier is required";
    if ($price <= 0) $errors[] = "Price must be greater than zero";
    
    // HANDLE IMAGE UPLOAD (REPLACEMENT)
    $image_path = $service['image_path']; // KEEP EXISTING IF NO NEW UPLOAD
    if (!empty($_FILES['image']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Only JPG, PNG, or GIF images allowed";
        } else {
            // DELETE OLD IMAGE IF EXISTS
            if (!empty($service['image_path'])) {
                $old_image_path = UPLOADS_DIR . $service['image_path'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            // UPLOAD NEW IMAGE
            $upload_dir = UPLOADS_DIR . 'services/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = 'service_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $filename;
            $target_path = str_replace('/', '\\', $target_path); // WINDOWS SAFE
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'services/' . $filename;
            } else {
                $errors[] = "Failed to upload new image. Check folder permissions.";
            }
        }
    }
    
    // UPDATE DATABASE IF NO ERRORS
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE services 
                SET title = ?, description = ?, category = ?, tier = ?, price = ?, image_path = ?, is_available = ?
                WHERE id = ? AND provider_id = ?
            ");
            $stmt->execute([
                $title,
                $description,
                $category,
                $tier,
                $price,
                $image_path,
                $is_available,
                $service_id,
                $_SESSION['user_id']
            ]);
            
            setAlert("Service updated successfully!", "success");
            header("Location: " . BASE_URL . "provider/dashboard.php");
            exit();
        } catch (PDOException $e) {
            error_log("Service update error: " . $e->getMessage());
            $errors[] = "Database error. Please try again.";
        }
    }
    
    // SHOW ERRORS
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .form-card { transition: transform 0.3s; }
        .form-card:hover { transform: translateY(-2px); }
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

    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-50 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">✨</span>
                    <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700">SAMAAROH</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>provider/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">← Back to Dashboard</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php displayAlert(); ?>

        <div class="text-center mb-10">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">Edit Service</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Update your service details. Changes are visible to customers immediately.
            </p>
        </div>

        <div class="form-card bg-white rounded-2xl border border-stone-200 shadow-lg p-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Service Title -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Service Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           value="<?= htmlspecialchars($service['title']) ?>">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea name="description" rows="4" 
                              class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"><?= htmlspecialchars($service['description']) ?></textarea>
                    <p class="text-xs text-stone-400 mt-1">Max 500 characters</p>
                </div>

                <!-- Category & Tier -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category" required 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                            <option value="das_bagiwala" <?= $service['category'] === 'das_bagiwala' ? 'selected' : '' ?>>🐎 Das Bagiwala (Chariot)</option>
                            <option value="party_plot" <?= $service['category'] === 'party_plot' ? 'selected' : '' ?>>🎪 Party Plot / Venue</option>
                            <option value="catering" <?= $service['category'] === 'catering' ? 'selected' : '' ?>>🍲 Catering</option>
                            <option value="photography" <?= $service['category'] === 'photography' ? 'selected' : '' ?>>📸 Photography & Videography</option>
                            <option value="decor" <?= $service['category'] === 'decor' ? 'selected' : '' ?>>🎨 Decor & Mandap</option>
                            <option value="entertainment" <?= $service['category'] === 'entertainment' ? 'selected' : '' ?>>🎤 Entertainment (DJ/Band)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Budget Tier <span class="text-rose-500">*</span></label>
                        <select name="tier" required 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                            <option value="standard" <?= $service['tier'] === 'standard' ? 'selected' : '' ?>>Standard (₹5-15 Lakhs)</option>
                            <option value="premium" <?= $service['tier'] === 'premium' ? 'selected' : '' ?>>Premium (₹15-30 Lakhs)</option>
                            <option value="luxury" <?= $service['tier'] === 'luxury' ? 'selected' : '' ?>>Luxury (₹30L+)</option>
                        </select>
                    </div>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Price (₹) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-stone-400">₹</span>
                        <input type="number" name="price" required step="0.01" min="0" 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                               value="<?= number_format($service['price'], 2, '.', '') ?>">
                    </div>
                </div>

                <!-- Availability -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_available" class="sr-only" <?= $service['is_available'] ? 'checked' : '' ?>>
                            <div class="block bg-stone-300 w-14 h-8 rounded-full"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                        </div>
                        <div class="ml-3 text-stone-700 font-medium">Make this service available for booking</div>
                    </label>
                </div>

                <!-- Current Image Preview -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Current Image</label>
                    <?php if (!empty($service['image_path'])): ?>
                        <div class="mb-4">
                            <img src="<?= UPLOADS_URL ?><?= htmlspecialchars($service['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($service['title']) ?>"
                                 class="max-h-64 rounded-xl border border-stone-200 object-contain">
                        </div>
                        <p class="text-xs text-stone-500">Replace with new image below (optional)</p>
                    <?php else: ?>
                        <p class="text-stone-400">No image uploaded yet</p>
                    <?php endif; ?>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Replace Image (Optional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-stone-300 border-dashed rounded-xl hover:border-rose-400 transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-stone-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-stone-600">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-rose-600 hover:text-rose-500">
                                    <span>Upload new image</span>
                                    <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-stone-500">PNG, JPG up to 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-stone-200 flex gap-4">
                    <button type="submit" 
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-lg transition flex items-center justify-center gap-2">
                        <span>✓ Save Changes</span>
                    </button>
                    <a href="<?= BASE_URL ?>provider/dashboard.php" 
                       class="flex-1 bg-stone-200 hover:bg-stone-300 text-stone-800 font-medium py-3 rounded-xl text-center transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-300 py-10 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex justify-center mb-6">
                <span class="text-4xl">✨</span>
                <h2 class="heading text-2xl font-bold text-white ml-2">SAMAAROH</h2>
            </div>
            <p class="max-w-2xl mx-auto mb-6">
                Nadiad's trusted wedding planning platform. Connecting families with verified vendors since 2026.
            </p>
            <p class="text-stone-500 text-sm">
                &copy; 2026 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.<br>
                BCA Final Year Project by Kishan Marwadi
            </p>
        </div>
    </footer>

    <script>
    // Toggle switch styling
    document.querySelector('input[name="is_available"]').addEventListener('change', function(e) {
        const dot = this.closest('label').querySelector('.dot');
        if (this.checked) {
            dot.style.transform = 'translateX(28px)';
            dot.style.backgroundColor = '#ef4444';
        } else {
            dot.style.transform = 'translateX(0)';
            dot.style.backgroundColor = '#ffffff';
        }
    });
    </script>
</body>
</html>