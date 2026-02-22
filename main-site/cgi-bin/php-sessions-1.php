<?php
session_start();
if(isset($_SESSION['username'])) {
    header("Location: php-sessions-2.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Session 1</title>
</head>
<body class="container mt-5">
    <h1>PHP Session Setup</h1>
    <form action="php-sessions-2.php" method="POST">
        <div class="form-group">
            <label>Enter your Name to Save in Session:</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <button type="submit">Save Session</button>
    </form>
</body>
</html>