<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include("../model/User.php");
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
