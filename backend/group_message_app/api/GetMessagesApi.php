<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include("../model/Message.php");

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
?>