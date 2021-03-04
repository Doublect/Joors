<?php

require_once 'Database.php';

class Chore implements IDBConvert, JsonSerializable
{
    public int $ID;
    public int $GroupID;
    public int $AssignID;
    public string $Name;
    public string $Colour;
    public string $Desc;
    public bool $Completed;
    public int $CreationTime;
    public int $Deadline;

    public static function fromRow(array $row) : Chore
    {
        $chore = new Chore();

        $chore->ID = $row['ID'] ?? -1;
        $chore->GroupID = $row['GroupID'] ?? -1;
        $chore->AssignID = $row['AssignID'] ?? -1;
        $chore->Name = $row['Name'] ?? "";
        $chore->Colour = $row['Colour'] ?? "";
        $chore->Desc = $row['Desc'] ?? "";
        $chore->Completed = $row['Completed'] ?? -1;
        $chore->CreationTime = $row['CreationTime'] ?? -1;
        $chore->Deadline = $row['Deadline'] ?? -1;

        return $chore;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Chore|false {
        if(($row = $stmt->execute()->fetchArray()) == false) {
            $return = false;
        } else {
            $return = Chore::fromRow($row);
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
            $return[0] = Chore::fromRow($row);

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
}

class ChoreDB extends Database
{
    private int $userID;

    function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // GET

    function getChore(int $choreID) : Chore|false
    {
        $stmt = $this->prepare("SELECT Chore.* FROM Chore, 'Group', UserGroup WHERE Chore.ID = :choreID AND UserGroup.AccountID = :userID");
        $stmt->bindValue(":choreID", $choreID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Chore::fetchSingle($stmt);
    }

    function getGroupsChores(int $groupID) : array|false
    {
        $stmt = $this->prepare("SELECT Chore.* FROM Chore, 'Group', UserGroup WHERE Chore.GroupID = :groupID AND UserGroup.AccountID = :userID");
        $stmt->bindValue(":groupID", $groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Chore::fetch($stmt);
    }

    // ------------------------------------------------------------------------
    // ADD

    function addChore(Chore $chore) : bool
    {
        $stmt = $this->prepare("INSERT INTO Chore (ID, GroupID, AssignID, Name, Colour, Desc, Completed, CreationTime, Deadline) VALUES (NULL, :groupID, :assignID, :name, :colour, :desc, :complete, :creation, :deadline)");

        $stmt->bindValue(":groupID", $chore->GroupID, SQLITE3_INTEGER);
        $stmt->bindValue(":assignID", $chore->AssignID, SQLITE3_INTEGER);
        $stmt->bindValue(":name", $chore->Name, SQLITE3_TEXT);
        $stmt->bindValue(":colour", $chore->Colour, SQLITE3_TEXT);
        $stmt->bindValue(":desc", $chore->Desc, SQLITE3_TEXT);
        $stmt->bindValue(":complete", $chore->Completed, SQLITE3_INTEGER);
        $stmt->bindValue(":creation", $chore->CreationTime, SQLITE3_INTEGER);
        $stmt->bindValue(":deadline", $chore->Deadline, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeChore(int $choreID) : bool
    {
        $stmt = $this->prepare("DELETE FROM Chore WHERE ID = :choreID and groupID IN (SELECT GroupID FROM UserGroup WHERE AccountID = :userID)");
        $stmt->bindValue(":choreID", $choreID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}

