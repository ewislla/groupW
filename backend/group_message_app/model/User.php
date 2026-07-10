<?php
include_once("../db/Connection.php");
include_once("../trait/BasicOperation.php");
include_once("../trait/Validate.php");
include_once("../trait/HashPassword.php");

class User extends Connection
{
    use BasicOperation, Validation, HashPassword;

    // defining the table name and column names for the user table
    protected string $table_name = 'users';
    protected string $column1 = 'name';
    protected string $column2 = 'password';
    protected string $column3 = 'api_token';

    public function __construct()
    {
        parent::__construct();
    }

    //register user function
    public function registerUser(string $name, string $password)
    {
        //validate the inputs
        $isValid = $this->validateRegistration($name, $password);

        if ($isValid !== true) {
            // FIXED: Return JSON-ready error instead of using die()
            return ["status" => "error", "message" => $isValid];
        }

        // check db to see if the user already exists
        if ($this->recordExists($this->table_name, $this->column1, $name)) {
        
            return ["status" => "error", "message" => "User already exists"];
        }

        // harshing password 
        $hashedPassword = $this->generateHash($password);

        //generate api token
        $token = bin2hex(random_bytes(32));

        //insert the user into the database
        $this->insertOperation(
            $this->table_name,
            $this->column1,
            $this->column2,
            $this->column3,
            $name,
            $hashedPassword,
            $token,
            'sss'
        );

        $newUserId = $this->connection->insert_id;

        return [
            "status" => "success",
            "message" => "User registered successfully",
            "api_token" => $token,
            "user_id" => $newUserId
        ];
    }

    // Look up a user using their secret API token
    public function getUserByToken(string $token)
    {
        return $this->fetchRecord($this->table_name, $this->column3, $token);
    }

    //login user function
    public function loginUser(string $name, string $password)
    {
        //  Validate first
        $isValid = $this->validateRegistration($name, $password);

        if ($isValid !== true) {
            return ["status" => "error", "message" => $isValid];
        }

        // fetch the user data
        $userData = $this->fetchRecord($this->table_name, $this->column1, $name);

        if (!$userData) {
            return ["status" => "error", "message" => "User does not exist Try registering first"];
        }

        if (!isset($userData['user_id'])) {
            return [
                "status" => "error",
                "message" => "Critical Server Error: 'user_id' column not found in the database return."
            ];
        }

        //verify password
        if (password_verify($password, $userData[$this->column2])) {
            return [
                "status" => "success",
                "message" => "Login successful",
                "api_token" => $userData[$this->column3],
                "user_id" => $userData['user_id']
            ];
        } else {
            return ["status" => "error", "message" => "Incorrect password"];
        }
    }
}
