<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include("../model/Message.php");

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

        $messages = $this->messageModel->getAllMessages();


        echo json_encode([
            "status" => "success",
            "data" => $messages
        ]);
    }
}

$api = new GetMessagesApi();
$api->processRequest();
