<?php
// create_users.php
$host = 'localhost';
$db   = 'analytics_db';
$user = 'tracker';
$pass = 'SuperSecretPassword123!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$users = [
    ['username' => 'superadmin', 'password' => 'adminpass', 'role' => 'super_admin', 'permissions' => json_encode(['all'])],
    ['username' => 'data_analyst', 'password' => 'analystpass', 'role' => 'analyst', 'permissions' => json_encode(['performance', 'behavior'])],
    ['username' => 'basic_viewer', 'password' => 'viewerpass', 'role' => 'viewer', 'permissions' => null]
];

foreach ($users as $u) {
    $hashed_password = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, permissions) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$u['username'], $hashed_password, $u['role'], $u['permissions']]);
        echo "Created user: {$u['username']}<br>";
    } catch (Exception $e) {
        echo "Error creating {$u['username']}: " . $e->getMessage() . "<br>";
    }
}
echo "Done! You can now delete this file.";
?>