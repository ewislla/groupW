<?php

trait HashPassword
{
    public function generateHash(string $raw_password)
    {
        $encrypted = password_hash($raw_password, PASSWORD_DEFAULT);
        return $encrypted;
    }
}
