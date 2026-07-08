<?php
include("../db/Connection.php");
include("../trait/BasicOperation.php");
include("../trait/Validate.php");
include("../trait/HashPassword.php");

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

            die($isValid);
        }

        // check db to see if the user already exists
        if ($this->recordExists($this->table_name, $this->column1, $name)) {
            die("User already exists");
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

        );

        return $token;
    }

    //login user function
    public function loginUser(string $name, string $password)
    {
        //  Validate first
        $isValid = $this->validateRegistration($name, $password);

        if ($isValid !== true) {
            return ["status" => "error", "message" => $isValid];
        }

        // fetech the user data from the database based on the provided name
        $userData = $this->fetchRecord($this->table_name, $this->column1, $name);


        if (!$userData) {
            return ["status" => "error", "message" => "User does not exist Try registering first"];
        }

        //verify password
        if (password_verify($password, $userData[$this->column2])) {
            // Password matches! Return the token
            return [
                "status" => "success",
                "message" => "Login successful",
                "api_token" => $userData[$this->column3]
            ];
        } else {
            // Password failed
            return ["status" => "error", "message" => "Incorrect password"];
        }
    }
}
