<?php

class Account
{
    private Database $db;
    private int $userID;

    function __construct($userid)
    {
        $this->db = new Database();
        $this->userID = $userid;
    }
}