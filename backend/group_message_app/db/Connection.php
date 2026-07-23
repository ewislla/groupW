<?php

class Connection
{
    public mysqli $connection;

    public function __construct()
    {
        $host = "mysql-wisdomit.alwaysdata.net";
        $username = 'wisdomit';
        $password = '$ITwisdom0';
        $database = 'wisdomit_group_message_app';

        $this->connection =  new mysqli($host, $username, $password, $database);
        if (!$this->connection) {
            die('Error' . $this->connection->connect_error);
        }
    }
}


new Connection();
