<?php
header("Content-Type: application/json");

$data = [
    'message' => 'Hello Team Xuanye',
    'language' => 'PHP',
    'time' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR']
];

echo json_encode($data);
?>