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
include("../trait/MailerTrait.php"); // 1. Include the Trait File

class SendMessageApi
{
    use MailerTrait; // 2. Use the trait inside the API class

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
        $message = $_POST['message'] ?? '';

        if (empty($token) || empty($message)) {
            echo json_encode(["status" => "error", "message" => "Token and message are required."]);
            return;
        }

        // Validate the user
        $user = $this->userModel->getUserByToken($token);
        $userId = $user['user_id'] ?? $user['user_id'] ?? null;

        if (!$userId) {
            echo json_encode(["status" => "error", "message" => "Unauthorized."]);
            return;
        }

        // Send the message to the database
        $response = $this->messageModel->sendMessage((int)$userId, $message);

        // 3. IF SUCCESSFUL: Trigger the Email Blast
        if ($response['status'] === 'success') {

            // Get all other users' emails
            $otherUsers = $this->userModel->getAllEmailsExcept((int)$userId);

            if (count($otherUsers) > 0) {
                $senderName = $user['name'] ?? 'Someone';
                $subject = "New Message from $senderName in GroupW";
                $htmlBody = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: #4f46e5;'>New Message in GroupW Global</h2>
                        <p><strong>$senderName</strong> just sent a message:</p>
                        <blockquote style='background: #f1f5f9; padding: 15px; border-left: 4px solid #4f46e5; border-radius: 4px;'>
                            " . htmlspecialchars($message) . "
                        </blockquote>
                        <p><a href='http://127.0.0.1:5500/frontend/harry/group_message_app/index.html' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 10px;'>Open GroupW</a></p>
                    </div>
                ";

                // Fire the trait method
                $this->SendEmail($otherUsers, $subject, $htmlBody);
            }
        }

        echo json_encode($response);
    }
}

$api = new SendMessageApi();
$api->processRequest();
