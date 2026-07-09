<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../model/User.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
class LoginApi
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function processRequest()
    {
        $name = $_POST['name'] ?? '';
        $password = $_POST['password'] ?? '';

        $response = $this->userModel->loginUser($name, $password);

        echo json_encode($response);
    }
}

$api = new LoginApi();
$api->processRequest();
