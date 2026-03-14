<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
// Note: We don't kick viewers out of this page!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Reports</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Saved Reports</h2>
        <a href="logout.php" style="padding: 8px 16px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">Logout</a>
    </div>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! (Role: <?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    <hr>
    <p>Your static, saved reports will be generated and displayed here soon.</p>
</body>
</html>