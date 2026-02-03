#!/usr/bin/php-cgi
<?php
// Standard CGI headers
header("Content-Type: application/json");
header("Cache-Control: no-cache");

// Get the raw POST data
$json = file_get_contents('php://input');
$request = json_decode($json, true);

$action = $request['action'] ?? '';
$fingerprint = $request['fp'] ?? '';
$userData = $request['data'] ?? '';

// File to store our "Database"
$dbFile = '../fp_db.json'; 

// Load existing DB
$db = [];
if (file_exists($dbFile)) {
    $db = json_decode(file_get_contents($dbFile), true);
}

$response = ["status" => "ok"];

if ($action === 'save') {
    // Map the fingerprint to the data
    $db[$fingerprint] = $userData;
    file_put_contents($dbFile, json_encode($db));
    $response["message"] = "Saved";
} 
elseif ($action === 'check') {
    // Start a PHP session to prove we can link it
    session_start();
    
    // Look up data by fingerprint
    if (isset($db[$fingerprint])) {
        $response["savedData"] = $db[$fingerprint];
        $response["message"] = "Restored from Fingerprint";
        // Re-establish session variable
        $_SESSION['user_data'] = $db[$fingerprint];
    } else {
        $response["savedData"] = null;
        $response["message"] = "New Visitor";
    }
}

echo json_encode($response);
?>