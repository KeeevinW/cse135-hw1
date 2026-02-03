<?php
header("Cache-Control: no-cache");
header("Content-Type: text/html");

// Read Raw Body
$message_body = file_get_contents('php://input');
?>
<!DOCTYPE html>
<html><head><title>General Request Echo</title>
</head><body><h1 align="center">General Request Echo</h1>
<hr>
<p><b>HTTP Protocol:</b> <?php echo $_SERVER['SERVER_PROTOCOL']; ?></p>
<p><b>HTTP Method:</b> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
<p><b>Query String:</b> <?php echo $_SERVER['QUERY_STRING']; ?></p>
<p><b>Message Body:</b> <?php echo $message_body; ?></p>
</body></html>