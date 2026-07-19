<?php
trait BasicOperation
{
    public function insertFaculty(string $name)
    {
        $sql = "INSERT INTO faculties (name) VALUES (?)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('s', $name);
        $results = $prepare->execute();

        if ($results === false) {
            die(json_encode(["status" => "error", "message" => "Error in adding to db"]));
        }
        return $results;
    }

    public function updateFaculty(string $name, int $faculty_id)
    {
        $sql = "UPDATE faculties SET name = ? WHERE faculty_id = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('si', $name, $faculty_id);
        $results = $prepare->execute();

        return $results !== false;
    }

    public function insertDepartment(string $name, int $faculty_id)
    {
        $sql = "INSERT INTO departments (name, faculty_id) VALUES (?, ?)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('si', $name, $faculty_id);
        $results = $prepare->execute();

        if ($results === false) {
            die(json_encode(["status" => "error", "message" => "Error in adding to db"]));
        }
        return $results;
    }

    public function updateDepartment(string $name, int $faculty_id, int $department_id)
    {
        $sql = "UPDATE departments SET name = ?, faculty_id = ? WHERE department_id = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('sii', $name, $faculty_id, $department_id);
        $results = $prepare->execute();

        return $results !== false;
    }

    public function recordExists(string $table_name, string $column1, string $value)
    {
        $sql = "SELECT * FROM $table_name WHERE $column1 = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('s', $value);
        $prepare->execute();

        $prepare->store_result();
        return $prepare->num_rows > 0;
    }

    public function fetchRecord(string $table_name, string $column1, string $value)
    {
        $sql = "SELECT * FROM $table_name WHERE $column1 = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('s', $value);
        $prepare->execute();

        $result = $prepare->get_result();
        return $result->fetch_assoc();
    }

    public function getAllRecords(string $table_name)
    {
        $sql = "SELECT * FROM $table_name";
        $prepare = $this->connection->prepare($sql);
        $result = $prepare->execute();
        $result = $prepare->get_result();

        if ($result === false) {
            die(json_encode(["status" => "error", "message" => "Error in fetching records from db"]));
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    //students operations 

    public function InsertStudent($first_name, $last_name, $middle_name, $gender, $matric_no, $email, $hashed_password, $dept_id, $fac_id)
    {
        $sql = "INSERT INTO Students(first_name, last_name, middle_name, gender, matric_no, email, password, department_id, faculty_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?,? )";

        $prepare = $this->connection->prepare($sql);

        $prepare->bind_param("sssssssii", $first_name, $last_name, $middle_name, $gender, $matric_no, $email, $hashed_password, $dept_id, $fac_id);

        return $prepare->execute();
    }

    // add user
    public function InsertUser($first_name, $last_name, $middle_name, $email, $password, $role, $tokens, $active)
    {
        $sql = "INSERT INTO users(first_name, last_name, middle_name, email, password, role, tokens,active) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param("ssssssss", $first_name, $last_name, $middle_name, $email, $password, $role, $tokens, $active);
        return $prepare->execute();
    }

    public function getSingleRecord(string $table, string $column, string $value)
    {
        $sql = "SELECT * FROM $table WHERE $column = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    public function setOTP(string $email, ?string $otp_code, ?string $otp_expiry)
    {
        $sql = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sss", $otp_code, $otp_expiry, $email);
        $result = $stmt->execute();

        return $result !== false;
    }

    public function updatePassword($new_password, $email)
    {
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param("ss", $new_password, $email);
        $results = $prepare->execute();
        return $results;
    }

    public function setDeviceAndToken(string $email, string $new_token, string $device_id)
    {
        $sql = "UPDATE users SET tokens = ?, current_device_id = ? WHERE email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sss", $new_token, $device_id, $email);
        $result = $stmt->execute();

        return $result !== false;
    }

    public function logoutDevice(string $token) 
    {
        $sql = "UPDATE users SET tokens = NULL, current_device_id = NULL WHERE tokens = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $token);
        $result = $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            return ["status" => "success", "message" => "Successfully logged out."];
        } else {
            return ["status" => "error", "message" => "Invalid session or already logged out."];
        }
    }
}
