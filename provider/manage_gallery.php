<?php
require_once '../config/config.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    setAlert("Access denied", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
if ($service_id <= 0) {
    setAlert("Invalid service", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// Verify service ownership
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND provider_id = ?");
$stmt->execute([$service_id, $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    setAlert("Service not found", "error");
    header("Location: " . BASE_URL . "provider/dashboard.php");
    exit();
}

// Handle gallery uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = UPLOADS_DIR . 'gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['gallery_images']['name'] as $key => $name) {
            if (!empty($name)) {
                $tmp_name = $_FILES['gallery_images']['tmp_name'][$key];
                $error = $_FILES['gallery_images']['error'][$key];
                
                if ($error === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_ext, $allowed_ext)) {
                        $unique_name = 'service_' . $service_id . '_' . time() . '_' . $key . '.' . $file_ext;
                        $upload_path = $upload_dir . $unique_name;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            // Save to database
                            $stmt = $pdo->prepare("
                                INSERT INTO service_gallery (service_id, image_name, image_path, display_order) 
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$service_id, $name, $unique_name, $key]);
                        }
                    }
                }
            }
        }
        
        setAlert("Gallery images uploaded successfully!", "success");
        header("Location: " . BASE_URL . "provider/manage_gallery.php?service_id=" . $service_id);
        exit();
    }
}

// Handle image deletion
if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    
    // Get image info before deletion
    $stmt = $pdo->prepare("SELECT * FROM service_gallery WHERE id = ? AND service_id = ?");
    $stmt->execute([$image_id, $service_id]);
    $image = $stmt->fetch();
    
    if ($image) {
        // Delete file
        $file_path = UPLOADS_DIR . 'gallery/' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM service_gallery WHERE id = ? AND service_id = ?");
        $stmt->execute([$image_id, $service_id]);
        
        setAlert("Image deleted successfully!", "success");
        header("Location: " . BASE_URL . "provider/manage_gallery.php?service_id=" . $service_id);
        exit();
    }
}

// Fetch gallery images
$gallery_stmt = $pdo->prepare("
    SELECT * FROM service_gallery 
    WHERE service_id = ? 
    ORDER BY display_order ASC, created_at DESC
");
$gallery_stmt->execute([$service_id]);
$gallery_images = $gallery_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery | <?= htmlspecialchars($service['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm sticky top-0 z-40 border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">✨</span>
                    <a href="<?= BASE_URL ?>" class="heading text-2xl font-bold tracking-tight text-rose-700">SAMAAROH</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>provider/dashboard.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">← Dashboard</a>
                    <a href="<?= BASE_URL ?>logout.php" class="text-stone-600 hover:text-rose-600 font-medium text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php displayAlert(); ?>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="heading text-3xl font-bold text-stone-800 mb-2">Manage Gallery</h1>
            <p class="text-stone-600"><?= htmlspecialchars($service['title']) ?></p>
        </div>

        <!-- Upload Section -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8 mb-8">
            <h2 class="font-bold text-xl text-stone-800 mb-6">Upload New Images</h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Gallery Images</label>
                    <div class="border-2 border-dashed border-stone-300 rounded-lg p-6 text-center hover:border-rose-500 transition">
                        <div class="text-stone-400 mb-2">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <input type="file" name="gallery_images[]" multiple accept="image/*" 
                               class="text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                        <p class="text-xs text-stone-500 mt-2">Upload multiple images (JPG, PNG, GIF)</p>
                    </div>
                </div>
                
                <button type="submit" 
                        class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Upload Images
                </button>
            </form>
        </div>

        <!-- Gallery Display -->
        <?php if (!empty($gallery_images)): ?>
        <div class="bg-white rounded-2xl border border-stone-200 p-8">
            <h2 class="font-bold text-xl text-stone-800 mb-6">Current Gallery (<?= count($gallery_images) ?> images)</h2>
            
            <div class="gallery-grid">
                <?php foreach ($gallery_images as $image): ?>
                <div class="relative group">
                    <img src="<?= UPLOADS_URL . 'gallery/' . htmlspecialchars($image['image_path']) ?>" 
                         alt="<?= htmlspecialchars($image['image_name']) ?>"
                         class="w-full h-48 object-cover rounded-lg">
                    
                    <!-- Delete Button -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition rounded-lg flex items-center justify-center">
                        <a href="?service_id=<?= $service_id ?>&delete_image=<?= $image['id'] ?>" 
                           onclick="return confirm('Delete this image?')"
                           class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Image Info -->
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg">
                        <p class="text-white text-xs truncate"><?= htmlspecialchars($image['image_name']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
            <div class="text-stone-300 text-6xl mb-4">📷</div>
            <h3 class="text-xl font-bold text-stone-800 mb-2">No Gallery Images Yet</h3>
            <p class="text-stone-500 mb-6">Upload images to showcase your work to customers</p>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="text-center mt-8">
            <a href="<?= BASE_URL ?>provider/edit_service.php?service_id=<?= $service_id ?>" 
               class="inline-block bg-stone-600 hover:bg-stone-700 text-white px-6 py-3 rounded-lg font-medium transition">
                ← Back to Edit Service
            </a>
        </div>
    </main>
</body>
</html>
