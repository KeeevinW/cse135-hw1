<?php
session_start();

// 1. FORCEFUL BROWSING PROTECTION
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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

// 3. FETCH RECENT DATA FOR TABLE
$stmt = $pdo->query("SELECT id, session_id, url, event_type, created_at FROM raw_logs ORDER BY created_at DESC LIMIT 50");
$logs = $stmt->fetchAll();

// 4. FETCH AGGREGATED DATA FOR CHART
// Getting the count of each event type (e.g., how many 'initial_load' vs 'activity_batch')
$chartStmt = $pdo->query("SELECT event_type, COUNT(*) as count FROM raw_logs GROUP BY event_type");
$chartData = $chartStmt->fetchAll();

// Prepare arrays for Chart.js
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .chart-container { width: 60%; margin: 40px 0; }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Analytics Dashboard</h2>
        <a href="logout.php" style="padding: 8px 16px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">Logout</a>
    </div>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    <hr>

    <h3>Event Frequency Chart</h3>
    <div class="chart-container">
        <canvas id="eventChart"></canvas>
    </div>

    <h3>Raw Collected Data</h3>
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

    <script>
        // Inject the PHP arrays into JavaScript as JSON
        const labels = <?php echo json_encode($chartLabels); ?>;
        const dataCounts = <?php echo json_encode($chartCounts); ?>;

        const ctx = document.getElementById('eventChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // You can change this to 'pie' or 'line' if you prefer
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
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>