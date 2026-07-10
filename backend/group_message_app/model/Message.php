<?php
include_once("../db/Connection.php");
include_once("../trait/BasicOperation.php");

class Message extends Connection
{

    use BasicOperation;
    protected string $table_name = 'messages';
    protected string $column1 = 'message';
    protected string $column2 = 'user_id';
    protected string $column3 = 'timestamp';

    public function __construct()
    {
        parent::__construct();
    }

    // to display all messages from the database
    public function sendMessage(string $messageContent, int $user_id, $timestamp)
    {
        // Insert the message into the database
        $this->insertOperation(
            $this->table_name,
            $this->column1,
            $this->column2,
            $this->column3,
            $messageContent,
            $user_id,
            $timestamp,
            'sii'
        );
    }
    // 1. Update the Fetch Method
    public function getAllMessages()
    {
        // Join using users.user_id and sort strictly by messages.message_id
        $sql = "SELECT messages.*, users.name 
                FROM " . $this->table_name . " 
                JOIN users ON messages.user_id = users.user_id 
                ORDER BY messages.message_id ASC";

        $result = $this->connection->query($sql);

        if ($result === false) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 2. Update the Edit Method
    public function editMessage(int $messageId, int $userId, string $newContent)
    {
        // Fetch using 'message_id'
        $message = $this->fetchRecord($this->table_name, 'message_id', (string)$messageId);

        if (!$message) {
            return ["status" => "error", "message" => "Message not found."];
        }

        if ((int)$message['user_id'] !== $userId) {
            return ["status" => "error", "message" => "Unauthorized: You can only edit your own messages."];
        }

        // Update using 'message_id'
        $success = $this->updateOperation(
            $this->table_name,
            $this->column1, // 'message' column
            'message_id',   // TARGETING EXACT COLUMN
            $newContent,
            $messageId,
            'si'
        );

        if ($success) {
            return ["status" => "success", "message" => "Message updated successfully."];
        } else {
            return ["status" => "error", "message" => "Database error while updating message."];
        }
    }
}


$hey = new Message();
// $hey->sendMessage('hey wisdom',1,1111);
$hey->getAllMessages();
