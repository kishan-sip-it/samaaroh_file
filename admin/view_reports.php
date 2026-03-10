<?php
require_once '../config/config.php';

// AUTH CHECK: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    setAlert("Admin access required", "error");
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// FETCH REPORTS AND FEEDBACK
$reports = $pdo->query("
    SELECT r.* 
    FROM reports r 
    ORDER BY r.created_at DESC
")->fetchAll();

$feedback = $pdo->query("
    SELECT f.* 
    FROM feedback f 
    ORDER BY f.created_at DESC
")->fetchAll();

// STATS
$stats = [
    'total_reports' => count($reports),
    'total_feedback' => count($feedback),
    'pending_reports' => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
    'resolved_reports' => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'resolved'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports | Samaaroh Admin</title>
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
            <h1 class="heading text-4xl font-bold text-stone-800">Reports & Feedback</h1>
            <p class="text-stone-500">Monitor user issues and platform feedback</p>
        </div>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-rose-600 mb-2"><?= $stats['total_reports'] ?></div>
                <div class="text-stone-500">Total Reports</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-amber-600 mb-2"><?= $stats['pending_reports'] ?></div>
                <div class="text-stone-500">Pending Reports</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-green-600 mb-2"><?= $stats['resolved_reports'] ?></div>
                <div class="text-stone-500">Resolved Reports</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-stone-200 text-center shadow-sm">
                <div class="text-4xl font-bold text-blue-600 mb-2"><?= $stats['total_feedback'] ?></div>
                <div class="text-stone-500">Total Feedback</div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm mb-8">
            <div class="border-b border-stone-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('reports')" id="reports-tab" class="py-4 px-6 border-b-2 border-rose-500 text-rose-600 font-medium">
                        📊 Reports (<?= count($reports) ?>)
                    </button>
                    <button onclick="showTab('feedback')" id="feedback-tab" class="py-4 px-6 border-b-2 border-transparent text-stone-500 hover:text-stone-700 font-medium">
                        💬 Feedback (<?= count($feedback) ?>)
                    </button>
                </nav>
            </div>
            
            <!-- Reports Tab -->
            <div id="reports-content" class="p-6">
                <?php if (empty($reports)): ?>
                    <div class="text-center py-12 text-stone-500">
                        <span class="text-4xl">📭</span>
                        <p class="mt-4 text-lg">No reports submitted yet</p>
                        <p class="text-sm mt-2">User reports will appear here when submitted</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reports as $report): ?>
                        <div class="border border-stone-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?= $report['status'] == 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' ?>">
                                            <?= ucfirst($report['status']) ?>
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-rose-100 text-rose-700">
                                            <?= ucfirst($report['priority']) ?>
                                        </span>
                                        <span class="text-xs text-stone-500">
                                            <?= date('M d, Y H:i', strtotime($report['created_at'])) ?>
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-stone-800"><?= htmlspecialchars($report['issue_type']) ?></h3>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-stone-800"><?= htmlspecialchars($report['name']) ?></div>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($report['email']) ?></div>
                                    <?php if ($report['phone']): ?>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($report['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="text-stone-600 mb-3">
                                <?= nl2br(htmlspecialchars($report['description'])) ?>
                            </div>
                            
                            <div class="text-xs text-stone-500 border-t pt-2">
                                Submitted on: <?= date('M d, Y H:i', strtotime($report['created_at'])) ?>
                            </div>
                            
                            <?php if ($report['status'] == 'pending'): ?>
                            <div class="mt-3 flex gap-2">
                                <form method="POST" action="resolve_report.php" class="inline">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                                        Mark Resolved
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Feedback Tab -->
            <div id="feedback-content" class="p-6 hidden">
                <?php if (empty($feedback)): ?>
                    <div class="text-center py-12 text-stone-500">
                        <span class="text-4xl">💬</span>
                        <p class="mt-4 text-lg">No feedback submitted yet</p>
                        <p class="text-sm mt-2">User feedback will appear here when submitted</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($feedback as $item): ?>
                        <div class="border border-stone-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="flex">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?= $i <= $item['rating'] ? 'text-amber-400' : 'text-stone-300' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-xs text-stone-500">
                                            <?= date('M d, Y H:i', strtotime($item['created_at'])) ?>
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-stone-800"><?= htmlspecialchars($item['service_type']) ?> - <?= htmlspecialchars($item['feedback_type']) ?></h3>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-stone-800"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($item['email']) ?></div>
                                    <?php if ($item['phone']): ?>
                                    <div class="text-sm text-stone-500"><?= htmlspecialchars($item['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="text-stone-600 mb-3">
                                <?= nl2br(htmlspecialchars($item['comments'])) ?>
                            </div>
                            
                            <div class="flex items-center gap-4 text-sm text-stone-500">
                                <span>Would recommend: <?= $item['would_recommend'] == 'yes' ? '✅ Yes' : '❌ No' ?></span>
                                <span>Submitted on: <?= date('M d, Y H:i', strtotime($item['created_at'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back to Dashboard -->
        <div class="text-center">
            <a href="<?= BASE_URL ?>admin/dashboard.php" class="inline-flex items-center gap-2 text-stone-600 hover:text-rose-600 transition">
                <span>←</span>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        function showTab(tab) {
            // Hide all content
            document.getElementById('reports-content').classList.add('hidden');
            document.getElementById('feedback-content').classList.add('hidden');
            
            // Remove active styles from tabs
            document.getElementById('reports-tab').classList.remove('border-rose-500', 'text-rose-600');
            document.getElementById('reports-tab').classList.add('border-transparent', 'text-stone-500');
            document.getElementById('feedback-tab').classList.remove('border-rose-500', 'text-rose-600');
            document.getElementById('feedback-tab').classList.add('border-transparent', 'text-stone-500');
            
            // Show selected content and activate tab
            if (tab === 'reports') {
                document.getElementById('reports-content').classList.remove('hidden');
                document.getElementById('reports-tab').classList.add('border-rose-500', 'text-rose-600');
                document.getElementById('reports-tab').classList.remove('border-transparent', 'text-stone-500');
            } else {
                document.getElementById('feedback-content').classList.remove('hidden');
                document.getElementById('feedback-tab').classList.add('border-rose-500', 'text-rose-600');
                document.getElementById('feedback-tab').classList.remove('border-transparent', 'text-stone-500');
            }
        }
    </script>
</body>
</html>
