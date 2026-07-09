<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../model/User.php");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
class RegisterApi
{
    private User $userModel;

    public function __construct()
    {
        // Create the User class as soon as the API class is created
        $this->userModel = new User();
    }

    public function processRequest()
    {
        $name = $_POST['name'] ?? '';
        $password = $_POST['password'] ?? '';


        $response = $this->userModel->registerUser($name, $password);

        echo json_encode($response);
    }
}


$api = new RegisterApi();
$api->processRequest();
