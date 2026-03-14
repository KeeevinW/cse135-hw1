<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'viewer') {
    header("Location: 403.php"); // Or wherever you handle forbidden access
    exit;
}

$host = 'localhost';
$db   = 'analytics_db';
$user = 'tracker';
$pass = 'SuperSecretPassword123!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT id, session_id, url, event_type, created_at FROM raw_logs ORDER BY created_at DESC LIMIT 50");
$logs = $stmt->fetchAll();
$chartStmt = $pdo->query("SELECT event_type, COUNT(*) as count FROM raw_logs GROUP BY event_type");
$chartData = $chartStmt->fetchAll();

$chartLabels = [];
$chartCounts = [];
foreach ($chartData as $row) {
    $chartLabels[] = $row['event_type'];
    $chartCounts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex h-screen">

    <div class="w-64 bg-gray-900 h-screen shadow-lg fixed">
        <div class="p-6">
            <h1 class="text-white text-2xl font-bold">Team Xuanye</h1>
            <p class="text-gray-400 text-sm mt-1">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
        </div>
        <nav class="mt-6">
            <a href="#performance" class="block py-3 px-6 text-gray-300 hover:text-white hover:bg-gray-800">1. Performance</a>
            <a href="#behavior" class="block py-3 px-6 text-gray-300 hover:text-white hover:bg-gray-800">2. User Behavior</a>
            <a href="#system" class="block py-3 px-6 text-gray-300 hover:text-white hover:bg-gray-800">3. System & Errors</a>
        </nav>
        <div class="absolute bottom-0 w-full">
            <a href="logout.php" class="block py-4 px-6 text-center text-white bg-red-600 hover:bg-red-700">Logout</a>
        </div>
    </div>

    <div class="flex-1 ml-64 overflow-y-auto">
        <div class="p-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Analytics Overview</h2>

            <section id="performance" class="mb-12 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold border-b pb-2 mb-4">1. Performance Metrics</h3>
                <div class="h-64 bg-gray-50 flex items-center justify-center border border-dashed border-gray-300 mb-4">
                    <span class="text-gray-400">[Chart.js Load Time Chart Placeholder]</span>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Analyst Comments</label>
                    <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" rows="3" placeholder="Enter interpretation of performance data..."></textarea>
                    <button class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Comment</button>
                </div>
            </section>

            <section id="behavior" class="mb-12 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold border-b pb-2 mb-4">2. User Behavior (Events)</h3>
                <div class="w-full max-w-2xl mx-auto mb-6">
                    <canvas id="eventChart"></canvas>
                </div>
                
                <div class="overflow-x-auto">
                    </div>
            </section>

            <section id="system" class="mb-12 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold border-b pb-2 mb-4">3. System & Errors</h3>
                 <div class="h-64 bg-gray-50 flex items-center justify-center border border-dashed border-gray-300 mb-4">
                    <span class="text-gray-400">[Chart.js Error Tracking Placeholder]</span>
                </div>
            </section>

        </div>
    </div>

    </body>
</html>