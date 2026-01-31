<?php
header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html>
<head><title>PHP Echo</title></head>
<body>
    <h1>PHP Echo</h1>
    <p><b>Method:</b> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
    <p><b>Protocol:</b> <?php echo $_SERVER['SERVER_PROTOCOL']; ?></p>

    <h3>Form Data Received:</h3>
    <ul>
        <?php
        // 1. Handle Standard GET/POST Data
        $params = $_REQUEST; 
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                echo "<li><b>" . htmlspecialchars($key) . ":</b> " . htmlspecialchars($value) . "</li>";
            }
        } else {
            echo "<li>No standard form data found.</li>";
        }
        ?>
    </ul>

    <?php
    // 2. Handle JSON Body (Raw Input)
    $rawInput = file_get_contents("php://input");
    if (!empty($rawInput)) {
        echo "<h3>Message Body (JSON/Raw):</h3>";
        echo "<pre>" . htmlspecialchars($rawInput) . "</pre>";
    }
    ?>
</body>
</html>