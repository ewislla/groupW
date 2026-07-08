<?php

trait BasicOperation
{
    public function insertOperation(
        string $table_name,
        string  $column1,
        string  $column2,
        string $column3,
        string $value1,
        string $value2,
        string $value3,
    ) {
        $sql = "INSERT INTO $table_name($column1, $column2, $column3) VALUES(?,?,?)";
        $prepare = $this->connection->prepare($sql);
        $prepare->bind_param('sss', $value1, $value2, $value3);
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
}
