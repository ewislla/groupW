<?php

trait BasicOperation
{
    public function insertOperation(
        string $table_name,
        string  $column1,
        string  $column2,
        string $column3,
        $value1,
        $value2,
        $value3,
        string $types
    ) {
        $sql = "INSERT INTO $table_name($column1, $column2, $column3) VALUES(?,?,?)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param($types, $value1, $value2, $value3);
        $results = $prepare->execute();
        if ($results === false) {
            die('Error in adding to db');
        }
        return $results;
    }


    public function recordExists(string $table_name, string $column1, string $value)
    {
        $sql = "SELECT * FROM $table_name WHERE $column1 = ?";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('s', $value);
        $prepare->execute();

        $prepare->store_result();

        // Returns true if 1 or more rows are found, false if 0
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
            die('Error in fetching records from db');
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOperation(
        string $table_name,
        string $column_to_update,
        string $where_column,
        $new_value,
        $where_value,
        string $types 
    ) {
        $sql = "UPDATE $table_name SET $column_to_update = ? WHERE $where_column = ?";
        $prepare = $this->connection->prepare($sql);
        
        $prepare->bind_param($types, $new_value, $where_value);
        $results = $prepare->execute();
        
        if ($results === false) {
            return false;
        }
        return true;
    }
}
