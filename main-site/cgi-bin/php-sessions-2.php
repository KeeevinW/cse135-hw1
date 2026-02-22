<?php
session_start();

if (isset($_POST['destroy'])) {
    session_destroy();
    header("Location: php-sessions-1.php");
    exit();
}

if (isset($_POST['username'])) {
    $_SESSION['username'] = $_POST['username'];
}

if (!isset($_SESSION['username'])) {
    header("Location: php-sessions-1.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>PHP Session 2</title></head>
<body style="font-family: sans-serif; padding: 2rem;">
    <h1>Session Data Retrieved</h1>
    <p>Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
    <p>This data is stored on the server.</p>
    
    <form method="POST">
        <input type="hidden" name="destroy" value="true">
        <button type="submit">Destroy Session</button>
    </form>
    <br>
    <a href="php-sessions-1.php">Go back to Page 1</a>
</body>
</html>