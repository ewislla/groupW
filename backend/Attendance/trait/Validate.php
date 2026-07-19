<?php
trait Validation
{
    public function ValidateStudentRegistration($first_name, $last_name, $gender, $matric_no, $email, $password, $dept_id, $fac_id)
    {
        if (empty(trim($first_name))) {
            return "First name is required";
        }
        if (empty(trim($last_name))) {
            return "Last name is required";
        }
        if (empty(trim($gender)) || ($gender !== 'Male' && $gender !== 'Female')) {
            return "Valid gender is required";
        }
        if (empty(trim($matric_no))) {
            return "Matriculation number is required";
        }
        if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "A valid email address is required";
        }
        if (empty(trim($password)) || strlen(trim($password)) < 6) {
            return "Password must be at least 6 characters long";
        }
        if (empty($dept_id)) {
            return "Department selection is required";
        }
        if (empty($fac_id)) {
            return "Faculty selection is required";
        }

        return "valid";
    }

    public function ValidateAddingUser($first_name, $last_name, $email, $password, $role, $active)
    {
        if (empty(trim($first_name))) {
            return "First name is required";
        }
        if (empty(trim($last_name))) {
            return "Last name is required";
        }
        if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "A valid email address is required";
        }
        if (empty(trim($password)) || strlen(trim($password)) < 6) {
            return "Password must be at least 6 characters long";
        }
        if (empty($role)) {
            return "Role selection is required";
        }
        if (empty($active)) {
            return "Active selection is required";
        }

        return "valid";
    }

    public function ValidateLogin(string $email, string $password)
    {
        if (empty(trim($email)) || empty(trim($password))) {
            return "Error: Name and password cannot be empty.";
        }

        return "valid";
    }

    public function ValidateReset(string $email)
    {
        if (empty(trim($email))) {
            return "Error: Can not be empty";
        }
        return "valid";
    }
}
