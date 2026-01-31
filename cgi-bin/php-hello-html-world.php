<?php
header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hello PHP</title>
</head>
<body>
    <h1>Hello Team Xuanye (PHP)</h1>
    <p>This page was generated with PHP CGI.</p>
    <p>Current Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p>Your IP Address: <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
</body>
</html>