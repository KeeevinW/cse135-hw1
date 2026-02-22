<?php
header("Cache-Control: no-cache");
header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html><head><title>GET Request Echo</title>
</head><body><h1 align="center">GET Request Echo</h1>
<hr>
<b>Query String:</b> <?php echo $_SERVER['QUERY_STRING']; ?><br />

<?php
// PHP automatically parses the query string into the $_GET array
foreach ($_GET as $key => $value) {
    echo "$key = $value<br/>\n";
}
?>
</body></html>