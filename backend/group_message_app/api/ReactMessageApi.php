<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../model/User.php");
include("../model/Message.php");

class ReactMessageApi
{
    private User $userModel;
    private Message $messageModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->messageModel = new Message();
    }

    public function processRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
            return;
        }

        $token = $_POST['api_token'] ?? '';
        $messageId = $_POST['message_id'] ?? '';
        $emoji = $_POST['emoji'] ?? '';

        if (empty($token) || empty($messageId) || empty($emoji)) {
            echo json_encode(["status" => "error", "message" => "Token, message ID, and emoji are required."]);
            return;
        }

        // Validate the user
        $user = $this->userModel->getUserByToken($token);
        $userId = $user['user_id'] ?? $user['id'] ?? null;

        if (!$userId) {
            echo json_encode(["status" => "error", "message" => "Unauthorized."]);
            return;
        }

        // Execute the reaction toggle
        $response = $this->messageModel->toggleReaction((int)$messageId, (int)$userId, $emoji);

        echo json_encode($response);
    }
}

$api = new ReactMessageApi();
$api->processRequest();
?>