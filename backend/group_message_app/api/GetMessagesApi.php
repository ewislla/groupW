<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../model/Message.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
class GetMessagesApi
{
    private Message $messageModel;

    public function __construct()
    {
        $this->messageModel = new Message();
    }

    public function processRequest()
    {
        // 1. Fetch the raw array of messages from the Model
        $messages = $this->messageModel->getAllMessages();

        // 2. Send them back directly without modifying them
        echo json_encode([
            "status" => "success",
            "data" => $messages
        ]);
    }
}

$api = new GetMessagesApi();
$api->processRequest();
