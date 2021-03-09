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
        $group->Name = $row['Name'] ?? '';
        $group->OwnerID = $row['OwnerID'] ?? -1;

        return $group;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Group|false
    {
        if(!($row = $stmt->execute()->fetchArray())) {
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

        if(!($row = $res->fetchArray())) {
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

    public function __construct($groupID)
    {
        $this->groupID = $groupID;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // CHECKS

    public function isMember(int $userID): bool
    {
        $stmt1 = $this->prepare("SELECT ID FROM UserGroup WHERE GroupID = ? AND UserID = ?");
        $stmt1->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt1->bindValue(2, $userID, SQLITE3_INTEGER);

        $stmt2 = $this->prepare("SELECT ID FROM Invitation WHERE GroupID = ? AND UserID = ?");
        $stmt2->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt2->bindValue(2, $userID, SQLITE3_INTEGER);


        return $this->exists($stmt1) || $this->exists($stmt2);
    }


    // ------------------------------------------------------------------------
    // GET

    public function getGroup(int $userID): Group|false
    {
        $stmt = $this->prepare("SELECT 'Group'.* FROM 'Group', UserGroup WHERE UserGroup.GroupID = ? AND UserGroup.UserID = ?");
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt);
    }

    public function getGroupByName(string $Name, int $ownerID): Group|false
    {
        $stmt = $this->prepare("SELECT 'Group'.* FROM 'Group' WHERE Name = ? AND OwnerID = ?");
        $stmt->bindValue(1, $Name, SQLITE3_TEXT);
        $stmt->bindValue(2, $ownerID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt);
    }

    public function getMembers(): array|false
    {
        $stmt = $this->prepare('SELECT ID, Name FROM User WHERE ID IN (SELECT UserID FROM UserGroup WHERE GroupID = ?)');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    public function getInvited(): array|false
    {
        $stmt = $this->prepare('SELECT ID, Name FROM User WHERE ID IN (SELECT UserID FROM Invitation WHERE GroupID = ?)');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        return Group::fetch($stmt);
    }

    public function getOwnerID(): int
    {
        $stmt =  $this->prepare('SELECT OwnerID FROM "Group" WHERE ID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt)->OwnerID;
    }

    // ------------------------------------------------------------------------
    // ADD

    public function addGroup(string $Name, int $userID): bool
    {
        $stmt = $this->prepare("INSERT INTO 'Group' (ID, Name, OwnerID) VALUES (NULL, ?, ?)");

        $stmt->bindValue(1, $Name, SQLITE3_TEXT);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);
        $this->finish($stmt);

        $this->groupID = $this->getGroupByName($Name, $userID)->ID;
        return $this->addMember($userID);
    }

    public function addMember(int $userID): bool
    {
        $stmt = $this->prepare('INSERT INTO UserGroup (ID, UserID, GroupID) VALUES (NULL, ?, ?)');
        $stmt->bindValue(1, $userID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $this->groupID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    public function inviteUser(int $userID): bool
    {
        $stmt = $this->prepare('INSERT INTO Invitation (ID, UserID, GroupID) VALUES (NULL, ?, ?)');
        $stmt->bindValue(1, $userID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $this->groupID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    public function removeGroup(int $userID): bool
    {
        $stmt = $this->prepare("DELETE FROM 'Group' WHERE ID = ? AND OwnerID = ?");
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        $this->finish($stmt);

        $stmt = $this->prepare("DELETE FROM 'UserGroup' WHERE GroupID = ?");
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $this->finish($stmt);

        $stmt = $this->prepare("DELETE FROM 'Task' WHERE GroupID = ?");
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    public function removeMember(int $userID): bool
    {
        $stmt = $this->prepare('DELETE FROM UserGroup WHERE GroupID = ? AND UserID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return $this->finish($stmt) | $this->removeInvitation($userID);
    }

    public function removeInvitation(int $userID): bool
    {
        $stmt = $this->prepare('DELETE FROM Invitation WHERE GroupID = ? AND UserID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE

}

