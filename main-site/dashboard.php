<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'viewer') {
    header("Location: 403.php");
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

$usersList = [];
if ($_SESSION['role'] === 'super_admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
        $deleteId = $_POST['delete_user_id'];
        // Prevent the super admin from accidentally deleting themselves
        if ($deleteId != $_SESSION['user_id']) {
            $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delStmt->execute([$deleteId]);
        }
        header("Location: dashboard.php");
        exit;
    }
    $userStmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
    $usersList = $userStmt->fetchAll();
}

$pageStmt = $pdo->query("
    SELECT 
        SUBSTRING_INDEX(url, '?', 1) as base_url, 
        COUNT(*) as hits 
    FROM raw_logs 
    WHERE event_type IN ('page_load', 'initial_load') 
    GROUP BY base_url 
    ORDER BY hits DESC 
    LIMIT 5
");
$pageData = $pageStmt->fetchAll();

$pageLabels = [];
$pageCounts = [];
foreach ($pageData as $row) {
    $cleanUrl = parse_url($row['base_url'], PHP_URL_PATH) ?: 'Homepage';
    
    // Fallback if the path is just a slash
    if ($cleanUrl === '/') {
        $cleanUrl = 'Homepage';
    }
    
    $pageLabels[] = $cleanUrl;
    $pageCounts[] = $row['hits'];
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
$perfData = array_reverse($perfStmt->fetchAll());

$perfLabels = [];
$perfTimes = [];
foreach ($perfData as $row) {
    $perfLabels[] = $row['time_label'];
    $perfTimes[] = $row['load_time'];
}

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: sans-serif; margin: 20px; color: #333; }
        .nav-bar { background: #eee; padding: 15px; border: 1px solid #ccc; margin-bottom: 20px; }
        .section-box { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background: #f9f9f9; }
        .chart-container { max-width: 600px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <noscript>
        <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border: 2px solid #b91c1c;">
            Warning: JavaScript is disabled in your browser. The charts and PDF export will not function properly.
        </div>
    </noscript>

    <div class="nav-bar">
        <h1>Team Xuanye</h1>
        <p>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
        <hr>
        <a href="#performance">1. Performance</a> | 
        <a href="#behavior">2. User Behavior</a> | 
        <a href="#system">3. System & Errors</a> 
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            | <a href="#admin-users">4. Manage Users</a>
        <?php endif; ?>
        | <a href="logout.php" style="color: red;">Logout</a>
    </div>

    <h2>Analytics Overview</h2>
    <button onclick="exportToPDF()" style="padding: 10px; cursor: pointer;">Export to PDF</button>
    <br><br>

    <div id="pdf-content">
        
        <div id="performance" class="section-box">
            <h3>1. Performance Metrics</h3>
            <div class="chart-container">
                <canvas id="performanceChart"></canvas>
            </div>
            
            <hr>
            <label><strong>Analyst Interpretation:</strong></label><br>
            <textarea id="perfComment" rows="3" cols="60" placeholder="Enter interpretation of performance data..."></textarea><br>
            <button id="saveCommentBtn" onclick="saveComment()" data-html2canvas-ignore>Save Comment</button>
            <span id="saveStatus" style="color: green; display: none;" data-html2canvas-ignore>Saved!</span>
        </div>

        <div id="behavior" class="section-box">
            <h3>2. User Behavior (Events)</h3>
            <div class="chart-container">
                <canvas id="eventChart"></canvas>
            </div>
            
            <h4>Recent Tracking Logs</h4>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Session ID</th>
                        <th>Page URL</th>
                        <th>Event Type</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['session_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['url'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['event_type'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No tracking data found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h4 class="text-md font-bold text-center mt-8 mb-2">Most Visited Pages</h4>
            <div class="chart-container">
                <canvas id="topPagesChart"></canvas>
            </div>
        </div>

        <div id="system" class="section-box">
            <h3>3. System Data</h3>
            <div class="chart-container" style="max-width: 300px;">
                <canvas id="systemChart"></canvas>
            </div>
        </div>

        

    </div>
    <?php if ($_SESSION['role'] === 'super_admin'): ?>
        <div id="admin-users" class="section-box" style="border-color: #d97706;">
            <h3>User Management</h3>
            <p>As a Super Admin, you have permission to view and revoke access for system users.</p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usersList as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars(strtoupper($u['role'])); ?></td>
                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                        <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <em>Current User</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <script>
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
            const element = document.getElementById('pdf-content');
            const opt = {
                margin:       0.5,
                filename:     'Team_Xuanye_Analytics_Report.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true }, 
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        const commentBox = document.getElementById('perfComment');
        const statusText = document.getElementById('saveStatus');

        if (localStorage.getItem('performanceComment')) {
            commentBox.value = localStorage.getItem('performanceComment');
        }

        function saveComment() {
            localStorage.setItem('performanceComment', commentBox.value);
            statusText.style.display = 'inline';
            setTimeout(() => statusText.style.display = 'none', 2000);
        }

        const pageLabels = <?php echo json_encode($pageLabels); ?>;
        const pageCounts = <?php echo json_encode($pageCounts); ?>;
        new Chart(document.getElementById('topPagesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: pageLabels,
                datasets: [{
                    label: 'Total Visits',
                    data: pageCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: { indexAxis: 'y', responsive: true }
        });
    </script>
</body>
</html>