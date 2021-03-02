<?php

class Account implements IDBConvert
{
    public int $ID;
    public string $Username;
    public string $Password;
    public int $CreationTime;

    public static function fromRow(array $row) : Account
    {
        $acc = new Account();

        $acc->ID = $row['ID'] ?? -1;
        $acc->Username = $row['Username'] ?? "";
        $acc->Password = $row['Password'] ?? "";
        $acc->CreationTime = $row['CreationTime'] ?? -1;

        return $acc;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Account|false {
        if(($row = $stmt->execute()->fetchArray()) == false) {
            $return = false;
        } else {
            $return = Account::fromRow($row);
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
            $return[0] = Account::fromRow($row);

            for($i = 1; ($row = $res->fetchArray()); $i++) {
                $return[$i] = $row;
            }
        }

        $stmt->close();
        return $return;
    }
}

class AccountDB extends Database
{
    private int $userID;

    function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // GET

    function getChore(int $choreID) : Account
    {
        $stmt = $this->prepare("SELECT Chore.* FROM Chore, 'Group', AccountGroup WHERE Chore.ID = :choreID AND AccountGroup.AccountID = :userID");
        $stmt->bindValue(":choreID", $choreID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Account::fetchSingle($stmt);
    }

    function getUser() : SQLite3Stmt
    {
        $stmt = $this->prepare("SELECT * FROM Account WHERE ID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $stmt;
    }

    function getUsersGroups() : Group|false
    {
        $stmt = $this->prepare("SELECT Group.* FROM 'Group', AccountGroup WHERE AccountGroup.AccountID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    // ------------------------------------------------------------------------
    // ADD

    function addUser(Account $acc) : bool
    {
        $stmt = $this->prepare("INSERT INTO Account(ID, Username, Password, CreationTime) VALUES (NULL, :uname, :passw, :creation)");
        $stmt->bindValue(":uname", $acc->Username, SQLITE3_TEXT);
        $stmt->bindValue(":passw", $acc->Password, SQLITE3_TEXT);
        $stmt->bindValue(":creation", $acc->CreationTime, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeUser() : bool
    {
        $stmt = $this->prepare("DELETE FROM Account WHERE ID = :userID");
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}