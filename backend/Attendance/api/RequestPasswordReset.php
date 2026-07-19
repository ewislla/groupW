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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$result = $userModel->requestPasswordReset($email);

// Check for "otp_sent" 
if (isset($result['status']) && $result['status'] === 'otp_sent') {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
?>