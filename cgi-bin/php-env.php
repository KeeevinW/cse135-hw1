<?php
header("Content-Type: text/html");
?>
<html>
<body>
    <h1>Environment Variables</h1>
    <ul>
        <?php
        foreach ($_SERVER as $key => $value) {
            echo "<li><b>$key:</b> $value</li>";
        }
        ?>
    </ul>
</body>
</html>