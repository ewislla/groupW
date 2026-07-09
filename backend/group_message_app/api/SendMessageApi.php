<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../model/User.php");
include_once("../model/Message.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class SendMessageApi
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
        $token = $_POST['api_token'] ?? '';
        $messageContent = $_POST['message'] ?? '';


        $user = $this->userModel->getUserByToken($token);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid API token."]);
            return;
        }


        $userId = (int)$user['user_id'];
        $timestamp = time();
        $this->messageModel->sendMessage($messageContent, $userId, $timestamp);


        echo json_encode([
            "status" => "success",
            "message" => "Message sent successfully"
        ]);
    }
}

$api = new SendMessageApi();
$api->processRequest();
