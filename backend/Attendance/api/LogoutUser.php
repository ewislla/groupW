<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../model/User.php';

$userModel = new User();


$token = isset($_POST['token']) ? trim($_POST['token']) : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Authentication token is required to log out."]);
    exit();
}

$result = $userModel->logoutUser($token);

if (isset($result['status']) && $result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
