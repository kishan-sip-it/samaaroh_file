<?php
require_once '../config/config.php';

// AUTH CHECK: Must be logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Please login as a service provider to add services", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $tier = $_POST['tier'];
    $price = floatval($_POST['price']);
    
    // VALIDATION
    $errors = [];
    if (empty($title)) $errors[] = "Service title is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($tier)) $errors[] = "Tier is required";
    if ($price <= 0) $errors[] = "Price must be greater than zero";
    
    // HANDLE IMAGE UPLOAD (WINDOWS-SAFE VERSION)
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "Image size must be less than 5MB";
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/services/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $filename = 'service_' . time() . '_' . $_SESSION['user_id'] . '.' . $file_ext;
            $upload_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/services/' . $filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO services (provider_id, title, description, category, tier, price, image_url, is_available, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $description,
                $category,
                $tier,
                $price,
                $image_path
            ]);
            
            setAlert("Service added successfully! It's now available for customers to book.", "success");
            header("Location: " . BASE_URL . "provider/my_services.php");
            exit();
            
        } catch (PDOException $e) {
            error_log("Service creation error: " . $e->getMessage());
            $errors[] = "Failed to create service. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        setAlert(implode("<br>", $errors), "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Service | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <?php include '../includes/navbar.php'; ?>
    
    <main class="max-w-4xl mx-auto px-4 py-8">
        <?php displayAlert(); ?>
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">Add New Service</h1>
            <p class="text-stone-500 mt-2">List your wedding service for Nadiad couples</p>
        </div>
        
        <!-- Service Form -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-lg">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Basic Information -->
                <div class="border-b border-stone-200 pb-6">
                    <h2 class="text-xl font-semibold text-stone-800 mb-4">Basic Information</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-stone-700 mb-2">
                                Service Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="e.g., Traditional Wedding Photography"
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-stone-700 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category" name="category" required
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Select a category</option>
                                <option value="photography" <?= ($_POST['category'] ?? '') === 'photography' ? 'selected' : '' ?>>Photography</option>
                                <option value="catering" <?= ($_POST['category'] ?? '') === 'catering' ? 'selected' : '' ?>>Catering</option>
                                <option value="decoration" <?= ($_POST['category'] ?? '') === 'decoration' ? 'selected' : '' ?>>Decoration</option>
                                <option value="venue" <?= ($_POST['category'] ?? '') === 'venue' ? 'selected' : '' ?>>Venue</option>
                                <option value="bagiwala" <?= ($_POST['category'] ?? '') === 'bagiwala' ? 'selected' : '' ?>>Das Bagiwala</option>
                                <option value="entertainment" <?= ($_POST['category'] ?? '') === 'entertainment' ? 'selected' : '' ?>>Entertainment</option>
                                <option value="makeup" <?= ($_POST['category'] ?? '') === 'makeup' ? 'selected' : '' ?>>Makeup & Beauty</option>
                                <option value="transport" <?= ($_POST['category'] ?? '') === 'transport' ? 'selected' : '' ?>>Transport</option>
                                <option value="other" <?= ($_POST['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-stone-700 mb-2">
                            Service Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                  placeholder="Describe your service in detail. What makes it special for Nadiad weddings?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- Pricing & Tier -->
                <div class="border-b border-stone-200 pb-6">
                    <h2 class="text-xl font-semibold text-stone-800 mb-4">Pricing & Service Level</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="tier" class="block text-sm font-medium text-stone-700 mb-2">
                                Service Tier <span class="text-red-500">*</span>
                            </label>
                            <select id="tier" name="tier" required
                                    class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Select service tier</option>
                                <option value="basic" <?= ($_POST['tier'] ?? '') === 'basic' ? 'selected' : '' ?>>Basic</option>
                                <option value="premium" <?= ($_POST['tier'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                                <option value="luxury" <?= ($_POST['tier'] ?? '') === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="price" class="block text-sm font-medium text-stone-700 mb-2">
                                Price (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="price" name="price" min="0" step="0.01" required
                                   class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="e.g., 15000"
                                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                            <p class="text-xs text-stone-500 mt-1">Enter your service price in Indian Rupees</p>
                        </div>
                    </div>
                </div>
                
                <!-- Service Image -->
                <div class="border-b border-stone-200 pb-6">
                    <h2 class="text-xl font-semibold text-stone-800 mb-4">Service Image</h2>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-stone-700 mb-2">
                            Upload Service Image
                        </label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <p class="text-xs text-stone-500 mt-1">
                            Upload a high-quality image of your service (JPG, PNG, GIF, max 5MB)
                        </p>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex gap-4">
                    <button type="submit" 
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Service
                    </button>
                    
                    <a href="<?= BASE_URL ?>provider/my_services.php" 
                       class="flex-1 bg-stone-200 hover:bg-stone-300 text-stone-800 font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Tips Section -->
        <div class="mt-8 bg-blue-50 rounded-2xl border border-blue-200 p-6">
            <h3 class="font-bold text-lg text-blue-800 mb-4">💡 Tips for Nadiad Wedding Services</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">📸 Service Images</h4>
                    <ul class="space-y-1 text-stone-700 text-sm">
                        <li>• Use high-quality, professional photos</li>
                        <li>• Show your best work from recent weddings</li>
                        <li>• Include multiple angles if possible</li>
                        <li>• Ensure good lighting and clarity</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">💰 Pricing Strategy</h4>
                    <ul class="space-y-1 text-stone-700 text-sm">
                        <li>• Research competitor pricing in Nadiad</li>
                        <li>• Consider your experience and quality</li>
                        <li>• Be transparent about what's included</li>
                        <li>• Offer different tiers for various budgets</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-blue-200">
                <h4 class="font-semibold text-blue-700 mb-2">📝 Description Best Practices</h4>
                <ul class="space-y-1 text-stone-700 text-sm">
                    <li>• Be specific about what you offer</li>
                    <li>• Mention your experience with Gujarati weddings</li>
                    <li>• Highlight unique selling points</li>
                    <li>• Include any special equipment or techniques</li>
                    <li>• Mention service duration and delivery timeline</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
