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
    
    // HANDLE IMAGE UPLOAD
    // HANDLE IMAGE UPLOAD (WINDOWS-SAFE VERSION)
$image_path = null;
if (!empty($_FILES['image']['name'])) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        $errors[] = "Only JPG, PNG, or GIF images allowed";
    } else {
        // CREATE UPLOADS FOLDER IF MISSING (WINDOWS-SAFE)
        $upload_dir = UPLOADS_DIR . 'services/';
        if (!is_dir($upload_dir)) {
            // Try to create directory with Windows permissions
            if (!mkdir($upload_dir, 0777, true)) {
                // Fallback: Try with backslashes (Windows style)
                $upload_dir = str_replace('/', '\\', $upload_dir);
                if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
                    $errors[] = "Cannot create upload folder. MANUALLY CREATE: C:\\wamp64\\www\\samaaroh_file\\uploads\\services\\";
                }
            }
        }
        
        if (empty($errors)) {
            // GENERATE UNIQUE FILENAME
            $filename = 'service_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $filename;
            
            // WINDOWS-SAFE: Convert to backslashes for move_uploaded_file
            $target_path = str_replace('/', '\\', $target_path);
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'services/' . $filename; // Store relative path
            } else {
                // DETAILED ERROR FOR DEBUGGING
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File too large (php.ini)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (form)',
                    UPLOAD_ERR_PARTIAL => 'Partial upload',
                    UPLOAD_ERR_NO_FILE => 'No file selected',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                    UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk (PERMISSIONS!)',
                    UPLOAD_ERR_EXTENSION => 'Extension blocked'
                ];
                $error_code = $_FILES['image']['error'];
                $error_msg = $upload_errors[$error_code] ?? 'Unknown error';
                $errors[] = "Upload failed: $error_msg (Code: $error_code). CHECK FOLDER PERMISSIONS!";
            }
        }
    }
}
    
    // INSERT INTO DATABASE IF NO ERRORS
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO services (provider_id, title, description, category, tier, price, image_path, is_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
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
            
            setAlert("Service added successfully! It's now visible to customers.", "success");
            header("Location: " . BASE_URL . "provider/dashboard.php");
            exit();
        } catch (PDOException $e) {
            error_log("Service insert error: " . $e->getMessage());
            $errors[] = "Database error. Please try again.";
        }
    }
    
    // IF ERRORS EXIST, SHOW THEM
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
    <title>Add Service | Samaaroh</title>
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
            <h1 class="heading text-3xl md:text-4xl font-bold text-stone-800">Add Your Service</h1>
            <p class="text-stone-500 mt-2 max-w-2xl mx-auto">
                Showcase your wedding service to families in Nadiad. Verified providers get priority visibility.
            </p>
        </div>

        <div class="form-card bg-white rounded-2xl border border-stone-200 shadow-lg p-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Service Title -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Service Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required 
                           class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="e.g., Traditional Das Buggy with Decorations"
                           value="<?= $_POST['title'] ?? '' ?>">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea name="description" rows="4" 
                              class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                              placeholder="Describe your service, inclusions, and what makes it special for Gujarati weddings..."><?= $_POST['description'] ?? '' ?></textarea>
                    <p class="text-xs text-stone-400 mt-1">Max 500 characters. Highlight Nadiad-specific experience.</p>
                </div>

                <!-- Category & Tier -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category" required 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                            <option value="">Select Category</option>
                            <option value="das_bagiwala" <?= ($_POST['category'] ?? '') === 'das_bagiwala' ? 'selected' : '' ?>>🛺 Das Bagiwala (Buggy)</option>
                            <option value="party_plot" <?= ($_POST['category'] ?? '') === 'party_plot' ? 'selected' : '' ?>>🎪 Party Plot / Venue</option>
                            <option value="catering" <?= ($_POST['category'] ?? '') === 'catering' ? 'selected' : '' ?>>🍲 Catering</option>
                            <option value="photography" <?= ($_POST['category'] ?? '') === 'photography' ? 'selected' : '' ?>>📸 Photography & Videography</option>
                            <option value="decor" <?= ($_POST['category'] ?? '') === 'decor' ? 'selected' : '' ?>>🎨 Decor & Mandap</option>
                            <option value="entertainment" <?= ($_POST['category'] ?? '') === 'entertainment' ? 'selected' : '' ?>>🎤 Entertainment (DJ/Band)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Budget Tier <span class="text-rose-500">*</span></label>
                        <select name="tier" required 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                            <option value="">Select Tier</option>
                            <option value="standard" <?= ($_POST['tier'] ?? '') === 'standard' ? 'selected' : '' ?>>Standard (₹5-15 Lakhs)</option>
                            <option value="premium" <?= ($_POST['tier'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium (₹15-30 Lakhs)</option>
                            <option value="luxury" <?= ($_POST['tier'] ?? '') === 'luxury' ? 'selected' : '' ?>>Luxury (₹30L+)</option>
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
                               placeholder="e.g., 15000"
                               value="<?= $_POST['price'] ?? '' ?>">
                    </div>
                    <p class="text-xs text-stone-400 mt-1">Enter base price for your service (excluding taxes)</p>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Service Image (Optional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-stone-300 border-dashed rounded-xl hover:border-rose-400 transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-stone-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-stone-600">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-rose-600 hover:text-rose-500">
                                    <span>Upload a file</span>
                                    <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-stone-500">PNG, JPG up to 5MB. Show your actual work in Nadiad!</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($_FILES['image']['name'])): ?>
                        <div class="mt-3">
                            <p class="text-sm text-green-600 font-medium">✓ File selected: <?= htmlspecialchars($_FILES['image']['name']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-stone-200">
                    <button type="submit" 
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl text-lg transition flex items-center justify-center gap-2">
                        <span>.Publish Service</span>
                        <span>→</span>
                    </button>
                    <p class="text-xs text-stone-400 text-center mt-3">
                        Your service will be visible to customers immediately. You can edit or delete it anytime from your dashboard.
                    </p>
                </div>
            </form>
        </div>

        <!-- Service Tips -->
        <div class="mt-12 bg-amber-50 rounded-2xl border border-amber-200 p-6">
            <h3 class="font-bold text-lg text-amber-800 mb-3">💡 Pro Tips for Nadiad Providers</h3>
            <ul class="space-y-2 text-stone-700 text-sm">
                <li class="flex items-start">
                    <span class="text-amber-500 mr-2 mt-1">✓</span>
                    <span><strong>Das Bagiwala?</strong> Mention if you serve Sangath, Mahudi Road, or central Nadiad areas</span>
                </li>
                <li class="flex items-start">
                    <span class="text-amber-500 mr-2 mt-1">✓</span>
                    <span><strong>Party Plot?</strong> Specify guest capacity and mandap inclusions</span>
                </li>
                <li class="flex items-start">
                    <span class="text-amber-500 mr-2 mt-1">✓</span>
                    <span>Upload <strong>real photos</strong> from actual weddings you've served in Nadiad</span>
                </li>
                <li class="flex items-start">
                    <span class="text-amber-500 mr-2 mt-1">✓</span>
                    <span>Customers have <strong>12 hours</strong> to book after seeing your service</span>
                </li>
            </ul>
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
                Nadiad's trusted wedding planning platform. Connecting families with verified vendors since 2024.
            </p>
            <p class="text-stone-500 text-sm">
                &copy; 2024 Samaaroh. Made with ❤️ in Nadiad for Gujarati weddings.<br>
                BCA Final Year Project by Kishan Marwadi
            </p>
        </div>
    </footer>

    <script>
    // Image preview on selection (enhanced UX)
    document.getElementById('image')?.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const preview = document.createElement('div');
            preview.className = 'mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 font-medium';
            preview.textContent = '✓ Selected: ' + fileName;
            this.closest('div').appendChild(preview);
            
            // Remove previous preview if exists
            const existing = this.closest('div').querySelector('.mt-3.bg-green-50');
            if (existing && existing !== preview) {
                existing.remove();
            }
        }
    });
    </script>
</body>
</html>