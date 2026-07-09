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
    public function getAllMessages()
    {
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
}


$hey = new Message();
// $hey->sendMessage('hey wisdom',1,1111);
$hey->getAllMessages();
