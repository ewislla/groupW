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


        $generated_token = $this->userModel->registerUser($name, $password);

        // 4. Send the successful JSON response back to the frontend
        echo json_encode([
            "status" => "success",
            "message" => "User registered successfully",
            "api_token" => $generated_token
        ]);
    }
}


$api = new RegisterApi();
$api->processRequest();
