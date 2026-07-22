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
    public function sendMessage(int $user_id, string $message)
    {
        // Insert the message into the database
        $success = $this->insertOperation(
            $this->table_name,
            'user_id',
            'message',
            'timestamp',
            $user_id,
            $message,
            date('Y-m-d H:i:s'),
            'iss' // integer, string, string
        );

        if ($success) {
            return ["status" => "success", "message" => "Message sent successfully."];
        } else {
            return ["status" => "error", "message" => "Failed to send message."];
        }
    }
    // 1. Update the Fetch Method
    public function getAllMessages()
    {
        // 1. Fetch all messages
        $sql = "SELECT messages.*, users.name 
                FROM " . $this->table_name . " 
                JOIN users ON messages.user_id = users.user_id 
                ORDER BY messages.message_id ASC";

        $result = $this->connection->query($sql);
        if ($result === false) return [];
        $messages = $result->fetch_all(MYSQLI_ASSOC);

        // 2. Fetch all reactions
        $reactSql = "SELECT r.message_id, r.user_id, r.emoji, u.name 
                     FROM reactions r 
                     JOIN users u ON r.user_id = u.user_id";
        $reactResult = $this->connection->query($reactSql);
        $reactions = $reactResult ? $reactResult->fetch_all(MYSQLI_ASSOC) : [];

        // 3. Group reactions by message_id
        $groupedReactions = [];
        foreach ($reactions as $r) {
            $groupedReactions[$r['message_id']][] = $r;
        }

        // 4. Attach reactions to their specific messages
        foreach ($messages as &$msg) {
            $msgId = $msg['message_id'];
            $msg['reactions'] = $groupedReactions[$msgId] ?? [];
        }

        return $messages;
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


    // Toggle a reaction (Add if it doesn't exist, Remove if it does)
    public function toggleReaction(int $messageId, int $userId, string $emoji)
    {
        // 1. Check if the user already reacted with this exact emoji
        $sql = "SELECT id FROM reactions WHERE message_id = ? AND user_id = ? AND emoji = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iis", $messageId, $userId, $emoji);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Reaction exists, so we toggle it OFF (Delete)
            $row = $result->fetch_assoc();
            $delSql = "DELETE FROM reactions WHERE id = ?";
            $delStmt = $this->connection->prepare($delSql);
            $delStmt->bind_param("i", $row['id']);
            $delStmt->execute();

            return ["status" => "success", "message" => "Reaction removed."];
        } else {
            // Reaction doesn't exist, toggle it ON (Insert)
            $insSql = "INSERT INTO reactions (message_id, user_id, emoji) VALUES (?, ?, ?)";
            $insStmt = $this->connection->prepare($insSql);
            $insStmt->bind_param("iis", $messageId, $userId, $emoji);
            $insStmt->execute();

            return ["status" => "success", "message" => "Reaction added."];
        }
    }


    // Fetch all user emails except the sender
    public function getAllEmailsExcept(int $excludeUserId)
    {
        $sql = "SELECT email FROM " . $this->table_name . " WHERE user_id != ? AND email IS NOT NULL AND email != ''";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $excludeUserId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
