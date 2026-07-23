<?php
include_once('../db/Connection.php');
include_once('../trait/BasicOperation.php');
include_once('../trait/Validate.php');

class Student extends Connection
{
    use BasicOperation, Validation;

    public function __construct()
    {
        parent::__construct();
    }

    public function RegisterStudent(
        string $first_name,
        string $last_name,
        string $middle_name,
        string $gender,
        string $matric_no,
        string $email,
        string $level,
        string $current_device_id, // NEW: Grabbed from the frontend
        int $dept_id,
        int $fac_id
    ) {
        // 1. Validate inputs (Ensure ValidateStudentRegistration handles the new parameters if needed)
        $validationResult = $this->ValidateStudentRegistration($first_name, $last_name, $gender, $matric_no, $email,  $dept_id, $fac_id);

        if ($validationResult !== "valid") {
            return ["status" => "error", "message" => $validationResult];
        }

        // 2. Prevent empty device IDs from sneaking through
        if (empty(trim($current_device_id))) {
            return ["status" => "error", "message" => "Device identification failed. Please ensure your browser allows local storage."];
        }

        // 3. Check for existing unique constraints
        if ($this->recordExists('students', 'email', $email)) {
            return ["status" => "error", "message" => "A student with this email already exists."];
        }

        if ($this->recordExists('students', 'matric_no', $matric_no)) {
            return ["status" => "error", "message" => "This matriculation number is already registered."];
        }

        // 4. Execute the insert operation
        $insertStatus = $this->InsertStudent($first_name, $last_name, $middle_name, $gender, $matric_no, $email, $level, $current_device_id, $dept_id, $fac_id);

        if ($insertStatus) {
            return ["status" => "success", "message" => "Registration successful. Your device is now linked."];
        } else {
            return ["status" => "error", "message" => "Failed to register student due to a database error."];
        }
    }
}