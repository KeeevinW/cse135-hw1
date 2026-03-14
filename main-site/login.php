<?php
session_start();
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Define database connection variables first!
    $host = 'localhost';
    $db   = 'analytics_db';
    $db_user = 'tracker'; // I changed this to $db_user so it doesn't conflict with your $user fetch below
    $db_pass = 'SuperSecretPassword123!';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    } catch (\PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    $stmt = $pdo->prepare("SELECT id, username, password, role, permissions FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // 3. Verify password and set session variables
    // IMPORTANT: Use password_verify() assuming you hash your passwords in the DB!
    // For testing with plain text (NOT recommended for final turn-in), use: if ($user && $password === $user['password'])
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // <-- THIS IS THE KEY TO RBAC
        $_SESSION['permissions'] = $user['permissions'];
        
        // Route them based on role 
        if ($user['role'] === 'viewer') {
            header("Location: saved_reports.php"); 
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Login</title>
</head>
<body>
    <h2>Analytics Login</h2>
    
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    
    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>