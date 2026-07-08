<?php

class Connection
{
    protected mysqli $connection;

    public function __construct()
    {
        $this->connection =  new mysqli('localhost', 'root', '', 'group_message_app');
        if (!$this->connection) {
            die('Error' . $this->connection->connect_error);
        } 
    }
}


