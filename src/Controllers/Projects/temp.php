<?php

// Enable CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = file_get_contents('php://input');
$args  = json_decode($input, true);

if (empty($args)) {
    $args = $_POST ?? [];
}

if (empty($args['uuid']) || empty($args['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: uuid, username, design, testbench',
    ]);
    exit;
}

try {

    if (! is_writable(__DIR__ . '/../hdlplayground')) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent directory is not writable: ' . realpath(__DIR__ . '/../'),
        ]);
        exit;
    }

    $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $args['username']);
    $uuid     = preg_replace('/[^a-zA-Z0-9_-]/', '', $args['uuid']);

    $dateFolder = date("Y-m-d");

    if (! is_dir(__DIR__ . '/../hdlplayground/' . $username)) {
        mkdir(__DIR__ . '/../hdlplayground/' . $username, 0777, true);
    }
    if (! is_dir(__DIR__ . '/../hdlplayground/' . $username . '/' . $dateFolder)) {
        mkdir(__DIR__ . '/../hdlplayground/' . $username . '/' . $dateFolder, 0777, true);
    }
    if (! is_dir(__DIR__ . '/../hdlplayground/' . $username . '/' . $dateFolder . '/' . $uuid)) {
        mkdir(__DIR__ . '/../hdlplayground/' . $username . '/' . $dateFolder . '/' . $uuid, 0777, true);
    }

    $directory = __DIR__ . '/../hdlplayground/' . $username . '/' . $dateFolder . '/' . $uuid;

    echo json_encode([
        'success' => true,
        'uuid'    => $uuid,
        'output'    => file_get_contents($directory.'/./output.log'),
        'error'    => file_get_contents($directory.'/./error.log'),
    ]);

} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ]);
}
