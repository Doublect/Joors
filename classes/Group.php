<?php

require_once 'Database.php';

class Group implements IDBConvert
{
    public int $ID;
    public string $Name;
    public int $OwnerID;

    public static function fromRow(array $row) : Group
    {
        $group = new Group();

        $group->ID = $row['ID'] ?? -1;
        $group->Name = $row['Name'] ?? "";
        $group->OwnerID = $row['OwnerID'] ?? -1;

        return $group;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Group|false
    {
        if(($row = $stmt->execute()->fetchArray()) == false) {
            $return = false;
        } else {
            $return = Group::fromRow($row);
        }

        $stmt->close();
        return $return;
    }

    public static function fetch(SQLite3Stmt $stmt) : array|false
    {
        $res = $stmt->execute();

        if(($row = $res->fetchArray()) == false) {
            $return = false;
        } else {
            $return = array();
            $return[0] = Group::fromRow($row);

            for($i = 1; ($row = $res->fetchArray()); $i++) {
                $return[$i] = $row;
            }
        }

        $stmt->close();
        return $return;
    }
}

class GroupDB extends Database
{
    private int $groupID;

    function __construct($groupID)
    {
        $this->groupID = $groupID;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // GET

    function getGroup(int $userID) : Group|false
    {
        $stmt = $this->prepare("SELECT 'Group'.* FROM 'Group', UserGroup WHERE UserGroup.GroupID = :groupID AND UserGroup.AccountID = :userID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt);
    }

    function getGroupByName(string $Name, int $ownerID) : Group|false
    {
        $stmt = $this->prepare("SELECT 'Group'.* FROM 'Group' WHERE Name = :name AND OwnerID = :userID");
        $stmt->bindValue(":name", $Name, SQLITE3_TEXT);
        $stmt->bindValue(":userID", $ownerID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt);
    }

    function getMembers() : array|false {
        $stmt = $this->prepare("SELECT User.ID, User.Name FROM User, UserGroup WHERE UserGroup.GroupID = :groupID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    // ------------------------------------------------------------------------
    // ADD

    function addGroup(string $Name, int $userID) : bool
    {
        $stmt = $this->prepare("INSERT INTO 'Group' (ID, Name, OwnerID) VALUES (NULL, :name, :userID)");

        $stmt->bindValue(":name", $Name, SQLITE3_TEXT);
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
        $this->finish($stmt);

        $this->groupID = $this->getGroupByName($Name, $userID)->ID;
        return  $this->addMember($userID);
    }

    function addMember(int $userID)
    {
        $stmt = $this->prepare("INSERT INTO UserGroup VALUES (NULL, :userID, :groupID)");
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeGroup(int $userID) : bool
    {
        $stmt = $this->prepare("DELETE FROM 'Group' WHERE ID = :groupID AND OwnerID = :userID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);

        $this->finish($stmt);

        $stmt = $this->prepare("DELETE FROM 'UserGroup' WHERE GroupID = :groupID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);
        $this->finish($stmt);

        $stmt = $this->prepare("DELETE FROM 'Task' WHERE GroupID = :groupID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    function removeMember(int $userID) : bool
    {
        $stmt = $this->prepare("DELETE FROM UserGroup WHERE GroupID = :groupID AND AccountID = :userID");
        $stmt->bindValue(":groupID", $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE

}

