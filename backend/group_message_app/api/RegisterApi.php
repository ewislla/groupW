<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include("../model/User.php");

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