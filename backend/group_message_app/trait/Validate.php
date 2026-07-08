<?php

trait Validation
{
    public function validateRegistration(string $name, string $password)
    {
        // 1. Check if name and password are not empty
        if (empty(trim($name)) || empty(trim($password))) {
            return "Error: Name and password cannot be empty.";
        }


        // 2. Check password length (e.g., minimum 6 characters)
        if (strlen(trim($password)) < 6) {
            return "Error: Password must be at least 6 characters long.";
        }
        
        return true;
    }
}
