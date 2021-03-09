<?php

require_once 'Database.php';
require_once 'Group.php';
require_once 'Input.php';

class User implements IDBConvert, JsonSerializable
{
    public int $ID;
    public string $Email;
    public string $Name;
    public string $Password;
    public int $CreationTime;

    public static function fromRow(array $row) : User
    {
        $user = new User();

        $user->ID = $row['ID'] ?? -1;
        $user->Name = $row['Name'] ?? '';
        $user->Password = $row['Password'] ?? '';
        $user->CreationTime = $row['CreationTime'] ?? -1;

        return $user;
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

    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }

    public static function jsonDeserialize($json) : User {
        $class = json_decode($json);
        $user = new User();

        foreach ($class AS $key => $value) {
            if($value !== null) {
                $user->{Input::test_input($key)} = Input::test_input($value);
            }
        }

        return $user;
    }
}

class UserDB extends Database
{
    private int $userID;

    public function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // CHECKS

    public function usernameExists(string $username): bool
    {
        $stmt = $this->prepare('SELECT Name FROM User WHERE Name = ?');
        $stmt->bindValue(1, $username, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->prepare('SELECT Name FROM User WHERE Email = :email');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    public function uniqueCheck(User $acc): bool
    {
        $stmt = $this->prepare('SELECT Name FROM User WHERE Email = :email OR Name = :uname');
        $stmt->bindValue(':email', $acc->Email, SQLITE3_TEXT);
        $stmt->bindValue(':uname', $acc->Name, SQLITE3_TEXT);

        return $this->exists($stmt);
    }

    // ------------------------------------------------------------------------
    // GET

    public function getUser(): User|false
    {
        $stmt = $this->prepare('SELECT * FROM User WHERE ID = :userID');
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        return User::fetchSingle($stmt);
    }

    public function getUserByName(string $username): User|false
    {
        $stmt = $this->prepare('SELECT * FROM User WHERE Name = :uname');
        $stmt->bindValue(':uname', $username, SQLITE3_TEXT);

        return User::fetchSingle($stmt);
    }

    public function getUsersGroups(): array|false
    {
        $stmt = $this->prepare("SELECT * FROM 'Group' WHERE ID in (SELECT GroupID FROM UserGroup WHERE UserID = ?)");
        $stmt->bindValue(1, $this->userID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    public function getInvitations(): array|false
    {
        $stmt = $this->prepare("SELECT * FROM 'Group' WHERE ID in (SELECT GroupID FROM Invitation WHERE UserID = ?)");
        $stmt->bindValue(1, $this->userID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    public function getTasks(): array|false
    {
        $stmt = $this->prepare("SELECT * FROM Task WHERE GroupID in (SELECT GroupID FROM UserGroup WHERE UserID = ?)");
    }

    // ------------------------------------------------------------------------
    // ADD

    public function addUser(User $acc): bool
    {
        $stmt = $this->prepare('INSERT INTO User(ID, Email, Name, Password, CreationTime) VALUES (NULL, :email, :uname, :passw, :creation)');
        $stmt->bindValue(':email', $acc->Email, SQLITE3_TEXT);
        $stmt->bindValue(':uname', $acc->Name, SQLITE3_TEXT);
        $stmt->bindValue(':passw', $acc->Password, SQLITE3_TEXT);
        $stmt->bindValue(':creation', $acc->CreationTime, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    public function removeUser(): bool
    {
        $stmt = $this->prepare('DELETE FROM User WHERE ID = :userID');
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}