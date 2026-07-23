<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once("../model/User.php");
require_once("../trait/MailerTrait.php");

class TestEmailApi {
    use MailerTrait;
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function test() {
        $sql = "SELECT email FROM users WHERE email IS NOT NULL AND email != ''";
        $result = $this->userModel->connection->query($sql);
        $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        if (empty($users)) {
            echo json_encode(["status" => "error", "message" => "No users with emails found in the database!"]);
            return;
        }

        $subject = "GroupW Messenger - Test Email Broadcast";
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #4f46e5;'>Bulk Email Test Successful!</h2>
                <p>This is a test notification broadcast sent to all registered users simultaneously using BCC.</p>
            </div>
        ";

        $response = $this->SendEmail($users, $subject, $htmlBody);
        echo json_encode($response);
    }
}

$api = new TestEmailApi();
$api->test();
?>