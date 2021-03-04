<?php

require_once 'Database.php';
require_once 'Input.php';

class User implements IDBConvert, JsonSerializable
{
    public int $ID;
    public string $Email;
    public string $Username;
    public string $Password;
    public int $CreationTime;

    public static function fromRow(array $row) : User
    {
        $acc = new User();

        $acc->ID = $row['ID'] ?? -1;
        $acc->Username = $row['Username'] ?? "";
        $acc->Password = $row['Password'] ?? "";
        $acc->CreationTime = $row['CreationTime'] ?? -1;

        return $acc;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : User|false {
        if(($row = $stmt->execute()->fetchArray()) == false) {
            $return = false;
        } else {
            $return = User::fromRow($row);
        }

        $stmt->close();
        return $return;
    }

    public static function fetch(SQLite3Stmt $stmt) : array|false {
        $res = $stmt->execute();

        if(($row = $res->fetchArray()) == false) {
            $return = false;
        } else {
            $return = array();
            $return[0] = User::fromRow($row);

            for($i = 1; ($row = $res->fetchArray()); $i++) {
                $return[$i] = $row;
            }
        }

        $stmt->close();
        return $return;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function jsonDeserialize($json) {
        $class = json_decode($json);
        foreach ($class AS $key => $value) {
            if($value != null)
                $this->{Input::test_input($key)} = Input::test_input($value);
        }
    }
}

class UserDB extends Database
{
    private int $userID;

    function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // CHECKS

    function usernameExists(string $username) : bool {
        $stmt = $this->prepare("SELECT Username FROM User WHERE Username = :uname");
        $stmt->bindValue(":uname", $username, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    function emailExists(string $email) : bool {
        $stmt = $this->prepare("SELECT Username FROM User WHERE Email = :email");
        $stmt->bindValue(":email", $email, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    function uniqueCheck(User $acc) : bool {
        $stmt = $this->prepare("SELECT Username FROM User WHERE Email = :email OR Username = :uname");
        $stmt->bindValue(":email", $acc->Email, SQLITE3_TEXT);
        $stmt->bindValue(":uname", $acc->Username, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    // ------------------------------------------------------------------------
    // GET

    function getUser() : User|false
    {
        $stmt = $this->prepare("SELECT * FROM User WHERE ID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return User::fetchSingle($stmt);
    }

    function getUserByName(string $username) : User|false
    {
        $stmt = $this->prepare("SELECT * FROM User WHERE Username = :uname");
        $stmt->bindValue(":uname", $username, SQLITE3_TEXT);

        return User::fetchSingle($stmt);
    }

    function getUsersGroups() : array|false
    {
        $stmt = $this->prepare("SELECT Group.* FROM 'Group', UserGroup WHERE UserGroup.AccountID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    // ------------------------------------------------------------------------
    // ADD

    function addUser(User $acc) : bool
    {
        $stmt = $this->prepare("INSERT INTO User(ID, Email, Username, Password, CreationTime) VALUES (NULL, :email, :uname, :passw, :creation)");
        $stmt->bindValue(":email", $acc->Email, SQLITE3_TEXT);
        $stmt->bindValue(":uname", $acc->Username, SQLITE3_TEXT);
        $stmt->bindValue(":passw", $acc->Password, SQLITE3_TEXT);
        $stmt->bindValue(":creation", $acc->CreationTime, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeUser() : bool
    {
        $stmt = $this->prepare("DELETE FROM User WHERE ID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}