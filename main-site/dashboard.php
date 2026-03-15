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
$perfStmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%H:%i:%s') as time_label, 
        JSON_UNQUOTE(JSON_EXTRACT(json_payload, '$.performance.totalLoadTime')) as load_time 
    FROM raw_logs 
    WHERE event_type = 'initial_load' AND JSON_EXTRACT(json_payload, '$.performance.totalLoadTime') IS NOT NULL
    ORDER BY created_at DESC 
    LIMIT 10
");
$perfData = array_reverse($perfStmt->fetchAll()); // Reverse so oldest is on the left of the chart

$perfLabels = [];
$perfTimes = [];
foreach ($perfData as $row) {
    $perfLabels[] = $row['time_label'];
    $perfTimes[] = $row['load_time'];
}

// --- 2. SYSTEM DATA (Connection Types) ---
// We extract connectionType from the JSON payload to see what networks users are on
$sysStmt = $pdo->query("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(json_payload, '$.static.connectionType')) as conn_type, 
        COUNT(*) as count 
    FROM raw_logs 
    WHERE event_type = 'initial_load' AND JSON_EXTRACT(json_payload, '$.static.connectionType') IS NOT NULL
    GROUP BY conn_type
");
$sysData = $sysStmt->fetchAll();

$sysLabels = [];
$sysCounts = [];
foreach ($sysData as $row) {
    $sysLabels[] = $row['conn_type'] ? strtoupper($row['conn_type']) : 'UNKNOWN';
    $sysCounts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-semibold text-gray-800">Analytics Overview</h2>
                <button onclick="exportToPDF()" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">
                    Export to PDF
                </button>
            </div>

            <div id="pdf-content">
                
                <section id="performance" class="mb-12 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold border-b pb-2 mb-4">1. Performance Metrics</h3>
                    <div class="w-full max-w-3xl mx-auto mb-4">
                        <canvas id="performanceChart"></canvas>
                    </div>
                    
                    <div class="mt-4" data-html2canvas-ignore> <label class="block text-sm font-medium text-gray-700">Analyst Comments</label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" rows="3" placeholder="Enter interpretation of performance data..."></textarea>
                        <button class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Comment</button>
                    </div>
                </section>

                <section id="behavior" class="mb-12 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold border-b pb-2 mb-4">2. User Behavior (Events)</h3>
                    
                    <div class="w-full max-w-2xl mx-auto mb-6">
                        <canvas id="eventChart"></canvas>
                    </div>
                    
                    <div class="overflow-x-auto mt-8">
                        <h4 class="text-lg font-semibold mb-2">Recent Tracking Logs</h4>
                        <table class="min-w-full border-collapse border border-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border border-gray-200 px-4 py-2 text-left text-gray-600">ID</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left text-gray-600">Session ID</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left text-gray-600">Page URL</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left text-gray-600">Event Type</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left text-gray-600">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $row): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="border border-gray-200 px-4 py-2"><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><?php echo htmlspecialchars($row['session_id'] ?? ''); ?></td>
                                            <td class="border border-gray-200 px-4 py-2 text-blue-600 truncate max-w-xs"><?php echo htmlspecialchars($row['url'] ?? ''); ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($row['event_type'] ?? ''); ?></span></td>
                                            <td class="border border-gray-200 px-4 py-2 text-gray-500"><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="border border-gray-200 px-4 py-4 text-center text-gray-500">No tracking data found yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="system" class="mb-12 bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold border-b pb-2 mb-4">3. System Data</h3>
                    <div class="w-full max-w-sm mx-auto mb-4">
                        <canvas id="systemChart"></canvas>
                    </div>
                </section>

            </div> </div>
    </div>

    

    <script>
        // Inject the PHP arrays into JavaScript as JSON for the Behavior Chart
        const labels = <?php echo json_encode($chartLabels); ?>;
        const dataCounts = <?php echo json_encode($chartCounts); ?>;

        const ctx = document.getElementById('eventChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Events',
                    data: dataCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        const perfLabels = <?php echo json_encode($perfLabels); ?>;
        const perfTimes = <?php echo json_encode($perfTimes); ?>;

        new Chart(document.getElementById('performanceChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: perfLabels,
                datasets: [{
                    label: 'Page Load Time (ms)',
                    data: perfTimes,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true }
        });

        const sysLabels = <?php echo json_encode($sysLabels); ?>;
        const sysCounts = <?php echo json_encode($sysCounts); ?>;

        new Chart(document.getElementById('systemChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: sysLabels,
                datasets: [{
                    label: 'Connection Types',
                    data: sysCounts,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(201, 203, 207, 0.6)'
                    ]
                }]
            },
            options: { responsive: true }
        });

        function exportToPDF() {
            // 1. Select the area you want to export
            const element = document.getElementById('pdf-content');
            
            // 2. Configure the PDF options
            const opt = {
                margin:       0.5,
                filename:     'Team_Xuanye_Analytics_Report.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true }, // scale: 2 makes the charts look sharp
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            // 3. Generate and save the PDF
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>