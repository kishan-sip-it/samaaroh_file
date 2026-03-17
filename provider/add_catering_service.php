<?php
include '../config/config.php';
include '../includes/header.php';

// Check if user is provider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Get provider's existing services
$stmt = $pdo->prepare("SELECT * FROM services WHERE provider_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$existing_services = $stmt->fetchAll();

// Handle file upload and menu creation
$menu_data = [];
$upload_error = '';
$menu_created = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['service_type']) && $_POST['service_type'] === 'catering') {
    
    // Check if file was uploaded
    if (isset($_FILES['menu_file']) && $_FILES['menu_file']['error'] === 0) {
        $file_name = $_FILES['menu_file']['name'];
        $file_tmp = $_FILES['menu_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_types = ['pdf', 'xlsx', 'xls', 'txt', 'doc', 'docx'];
        if (!in_array($file_ext, $allowed_types)) {
            $upload_error = "Invalid file type. Please upload PDF, Excel, or Text file.";
        } else {
            // Parse uploaded file
            $menu_data = parse_menu_file($file_tmp, $file_ext);
            
            if (!empty($menu_data)) {
                // Save menu to database
                $stmt = $pdo->prepare("INSERT INTO services (
                    provider_id, 
                    service_name, 
                    service_type, 
                    description, 
                    price, 
                    menu_data, 
                    status, 
                    created_at
                ) VALUES (?, ?, 'catering', ?, ?, ?, 'available', NOW())");
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_POST['service_name'] ?? 'Catering Service',
                    $_POST['description'] ?? 'Professional catering services',
                    $_POST['price'] ?? 500,
                    json_encode($menu_data)
                ]);
                
                $menu_created = true;
            } else {
                $upload_error = "Could not parse menu file. Please check the format.";
            }
        }
    } elseif (isset($_POST['use_built_in'])) {
        // Use built-in thali
        $menu_data = get_built_in_thali();
        
        $stmt = $pdo->prepare("INSERT INTO services (
            provider_id, 
            service_name, 
            service_type, 
            description, 
            price, 
            menu_data, 
            status, 
            created_at
        ) VALUES (?, ?, 'catering', ?, ?, ?, 'available', NOW())");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['service_name'] ?? 'Traditional Gujarati Thali',
            $_POST['description'] ?? 'Authentic Gujarati thali with traditional items',
            $_POST['price'] ?? 600,
            json_encode($menu_data)
        ]);
        
        $menu_created = true;
    }
}

// Function to parse different file types
function parse_menu_file($file_path, $file_ext) {
    $menu_items = [];
    
    try {
        switch ($file_ext) {
            case 'pdf':
                $menu_items = parse_pdf_menu($file_path);
                break;
            case 'xlsx':
            case 'xls':
                $menu_items = parse_excel_menu($file_path);
                break;
            case 'txt':
            case 'doc':
            case 'docx':
                $menu_items = parse_text_menu($file_path);
                break;
        }
    } catch (Exception $e) {
        error_log("Menu parsing error: " . $e->getMessage());
        return [];
    }
    
    return convert_to_checklist($menu_items);
}

// Simple text parser (can be enhanced)
function parse_text_menu($file_path) {
    $content = file_get_contents($file_path);
    $lines = explode("\n", $content);
    $menu_items = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            // Try to extract price
            if (preg_match('/(.+?)\s*(₹\d+|\d+)/', $line, $matches)) {
                $menu_items[] = [
                    'name' => trim($matches[1]),
                    'price' => extract_price($matches[2]),
                    'category' => 'general'
                ];
            }
        }
    }
    
    return $menu_items;
}

// Built-in Gujarati Thali
function get_built_in_thali() {
    return [
        'categories' => [
            'farsan' => [
                ['name' => 'Fafda-Jalebi', 'price' => 80, 'description' => 'Traditional Gujarati sweet'],
                ['name' => 'Ganthia', 'price' => 60, 'description' => 'Crispy snack'],
                ['name' => 'Sev Mamra', 'price' => 70, 'description' => 'Mixture snack'],
                ['name' => 'Chakri', 'price' => 50, 'description' => 'Spiral snack']
            ],
            'main_course' => [
                ['name' => 'Dal Fry', 'price' => 120, 'description' => 'Spicy lentil preparation'],
                ['name' => 'Shaak', 'price' => 100, 'description' => 'Seasonal vegetables'],
                ['name' => 'Kadhi', 'price' => 110, 'description' => 'Gram flour curry'],
                ['name' => 'Roti/Phulka', 'price' => 40, 'description' => 'Fresh Indian bread'],
                ['name' => 'Rice', 'price' => 60, 'description' => 'Steamed basmati rice']
            ],
            'sweet_dish' => [
                ['name' => 'Shrikhand', 'price' => 90, 'description' => 'Sweet yogurt dessert'],
                ['name' => 'Aam Ras', 'price' => 70, 'description' => 'Mango pulp drink'],
                ['name' => 'Basundi', 'price' => 100, 'description' => 'Thick sweetened milk']
            ],
            'pickle_accompaniment' => [
                ['name' => 'Methia Thepla', 'price' => 80, 'description' => 'Fenugreek flatbread'],
                ['name' => 'Athana', 'price' => 60, 'description' => 'Spicy pickle'],
                ['name' => 'Chhundo', 'price' => 70, 'description' => 'Sweet pickle']
            ]
        ],
        'pricing' => [
            'per_plate' => 600,
            'min_guests' => 50,
            'includes' => ['Unlimited servings', 'Traditional packaging', 'Delivery within 10km']
        ]
    ];
}

// Convert to interactive checklist format
function convert_to_checklist($menu_items) {
    $checklist = [];
    
    foreach ($menu_items as $item) {
        $checklist[] = [
            'id' => uniqid('item_'),
            'name' => $item['name'],
            'price' => $item['price'],
            'description' => $item['description'] ?? '',
            'category' => $item['category'] ?? 'general',
            'selected' => false
        ];
    }
    
    return $checklist;
}

// Extract price from string
function extract_price($price_str) {
    return (int) preg_replace('/[^\d]/', '', $price_str);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Catering Service | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .file-upload { border: 2px dashed #cbd5e1; transition: all 0.3s; }
        .file-upload:hover { border-color: #dc2626; background-color: #fef2f2; }
        .file-upload.dragover { border-color: #dc2626; background-color: #fef2f2; }
    </style>
</head>
<body class="bg-stone-50">
    <?php include '../includes/navbar.php'; ?>

    <!-- Header Section -->
    <section class="bg-gradient-to-r from-rose-600 to-amber-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4">Add Catering Service</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Upload your existing menu or use our built-in Gujarati thali
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <?php if ($menu_created): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-8">
                    <strong>Success!</strong> Your catering service has been added successfully.
                    <a href="<?= BASE_URL ?>provider/dashboard.php" class="text-green-600 underline ml-2">View Dashboard</a>
                </div>
            <?php endif; ?>
            
            <?php if ($upload_error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-8">
                    <strong>Error!</strong> <?= $upload_error ?>
                </div>
            <?php endif; ?>

            <!-- Service Creation Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Catering Service Details</h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Service Name</label>
                            <input type="text" name="service_name" required
                                   class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                   placeholder="e.g., Premium Gujarati Catering">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Base Price Per Plate</label>
                            <input type="number" name="price" required min="50" step="10"
                                   class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                   placeholder="e.g., 600">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Description</label>
                        <textarea name="description" rows="4" required
                                  class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                  placeholder="Describe your catering service, specialties, and experience..."></textarea>
                    </div>
                    
                    <input type="hidden" name="service_type" value="catering">
                </form>
            </div>

            <!-- Menu Upload Options -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Menu Options</h2>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- File Upload Option -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-stone-700">📄 Upload Your Menu</h3>
                        <p class="text-stone-600 mb-4">Upload your existing menu in PDF, Excel, or Text format</p>
                        
                        <div class="file-upload rounded-xl p-8 text-center cursor-pointer" 
                             ondrop="handleDrop(event)" 
                             ondragover="handleDragOver(event)" 
                             ondragleave="handleDragLeave(event)">
                            <div class="text-4xl mb-4">📁</div>
                            <p class="text-stone-700 font-semibold mb-2">Drop file here or click to browse</p>
                            <p class="text-sm text-stone-500">PDF, Excel, Word, or Text files</p>
                            <input type="file" name="menu_file" id="menu_file" 
                                   accept=".pdf,.xlsx,.xls,.txt,.doc,.docx"
                                   class="hidden" onchange="handleFileSelect(this)">
                        </div>
                        
                        <div id="file_info" class="mt-4 hidden">
                            <div class="bg-rose-50 p-4 rounded-lg">
                                <p class="font-semibold text-rose-700">Selected File:</p>
                                <p id="file_name" class="text-stone-600"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Built-in Thali Option -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-stone-700">🍽 Use Built-in Thali</h3>
                        <p class="text-stone-600 mb-4">Start with our traditional Gujarati thali template</p>
                        
                        <div class="bg-amber-50 rounded-xl p-6">
                            <h4 class="font-bold text-lg mb-3 text-amber-800">Traditional Gujarati Thali</h4>
                            
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center">
                                    <span class="text-amber-600 mr-2">🥟</span>
                                    <span class="text-stone-700">Farsan (Fafda, Ganthia, Sev)</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-amber-600 mr-2">🍛</span>
                                    <span class="text-stone-700">Main Course (Dal, Shaak, Kadhi)</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-amber-600 mr-2">🍮</span>
                                    <span class="text-stone-700">Sweet Dish (Shrikhand, Aam Ras)</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-amber-600 mr-2">🥙</span>
                                    <span class="text-stone-700">Pickle (Methia, Athana)</span>
                                </div>
                            </div>
                            
                            <button type="submit" form="main_form" name="use_built_in" value="1"
                                    class="w-full bg-amber-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-amber-700 transition">
                                Use This Thali Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" form="main_form" 
                        class="bg-rose-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-rose-700 transition transform hover:scale-105 shadow-xl">
                    Save Catering Service
                </button>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
    let selectedFile = null;

    function handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('menu_file').files = files;
            handleFileSelect({ target: { files: files } });
        }
    }

    function handleFileSelect(input) {
        const file = input.files[0];
        if (file) {
            selectedFile = file;
            document.getElementById('file_name').textContent = file.name;
            document.getElementById('file_info').classList.remove('hidden');
        }
    }

    // Auto-submit when file is selected
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('menu_file');
        const mainForm = document.querySelector('form');
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                // Auto-submit form after file selection
                setTimeout(() => {
                    mainForm.submit();
                }, 1000);
            }
        });
    });
    </script>
</body>
</html>
