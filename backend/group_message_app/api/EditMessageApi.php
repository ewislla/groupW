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

class EditMessageApi
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
        $newContent = $_POST['new_message'] ?? '';

        if (empty($token) || empty($messageId) || empty($newContent)) {
            echo json_encode(["status" => "error", "message" => "Token, message ID, and new message content are required."]);
            return;
        }

        $user = $this->userModel->getUserByToken($token);

        if (!$user || !isset($user['user_id'])) {
            echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid API token."]);
            return;
        }

        $userId = (int)$user['user_id'];
        $messageId = (int)$messageId;

        $response = $this->messageModel->editMessage($messageId, $userId, $newContent);

        echo json_encode($response);
    }
}

$api = new EditMessageApi();
$api->processRequest();
