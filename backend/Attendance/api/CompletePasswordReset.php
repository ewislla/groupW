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
$otp_code = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

if (empty($email) || empty($otp_code) || empty($new_password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email, OTP code, and new password are required"]);
    exit();
}

$result = $userModel->completePasswordReset($email, $otp_code, $new_password);

// Check for final "success" status
if (isset($result['status']) && $result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($result);
?>