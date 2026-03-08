<?php
session_start();

// FORCEFUL BROWSING PROTECTION:
// If the session variable isn't set, redirect them back to login immediately
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>You have successfully logged in. This page is protected.</p>
    
    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>