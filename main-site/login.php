<?php
session_start(); // Start the session at the very top
$error = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded credentials for the checkpoint
    // (You can change these to whatever you want the grader to use)
    if ($username === 'admin' && $password === 'password123') {
        // Success! Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        
        // Redirect to the protected dashboard
        header("Location: dashboard.php");
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
    <h2>Analytics System Login</h2>
    
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