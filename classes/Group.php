<?php

require_once 'Database.php';
require_once 'User.php';

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

class LoadPair
{
    public int $UserID;
    public int $Amount;

    public function __construct(int $UserID, int $Amount)
    {
        $this->UserID = $UserID;
        $this->Amount = $Amount;
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
        $stmt1 = $this->prepare('SELECT ID FROM UserGroup WHERE GroupID = ? AND UserID = ?');
        $stmt1->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt1->bindValue(2, $userID, SQLITE3_INTEGER);

        $stmt2 = $this->prepare('SELECT ID FROM Invitation WHERE GroupID = ? AND UserID = ?');
        $stmt2->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt2->bindValue(2, $userID, SQLITE3_INTEGER);


        return $this->exists($stmt1) || $this->exists($stmt2);
    }


    // ------------------------------------------------------------------------
    // GET

    public function getGroup(int $userID): Group|false
    {
        $stmt = $this->prepare('SELECT "Group".* FROM "Group", UserGroup WHERE UserGroup.GroupID = ? AND UserGroup.UserID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt);
    }

    public function getGroupByName(string $Name, int $ownerID): Group|false
    {
        $stmt = $this->prepare('SELECT "Group".* FROM "Group" WHERE Name = ? AND OwnerID = ?');
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
        $stmt = $this->prepare('SELECT OwnerID FROM "Group" WHERE ID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        return Group::fetchSingle($stmt)->OwnerID;
    }

    public function getUserLoad(): array|false
    {
        $stmt = $this->prepare('SELECT UserID, Load FROM UserGroup WHERE GroupID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        $res = $stmt->execute();
        $return = array();

        if(!($row = $res->fetchArray())) {
            $stmt->close();
            return false;
        }

        do {
            $load = new LoadPair($row['UserID'], $row['Load']);

            $return[] = $load;
        } while ($row = $res->fetchArray());

        $stmt->close();
        return $return;
    }

    public function getMinimumLoad(): int|false
    {
        $stmt = $this->prepare('SELECT UserID, Load FROM UserGroup WHERE GroupID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);

        $res = $stmt->execute();

        if(!($row = $res->fetchArray())) {
            $stmt->close();
            return false;
        }

        $minLoad = PHP_INT_MAX;
        $id = -1;

        do {
            if($row['Load'] < $minLoad) {
                $minLoad = $row['Load'];
                $id = $row['UserID'];
            }
        } while ($row = $res->fetchArray());

        return $id;
    }

    // ------------------------------------------------------------------------
    // ADD

    public function addGroup(string $Name, int $userID): bool
    {
        $stmt = $this->prepare('INSERT INTO "Group" (ID, Name, OwnerID) VALUES (NULL, ?, ?)');

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
        // Remove the group
        $stmt = $this->prepare('DELETE FROM "Group" WHERE ID = ? AND OwnerID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        $this->finish($stmt);

        // Remove membership
        $stmt = $this->prepare('DELETE FROM UserGroup WHERE GroupID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $this->finish($stmt);

        // Remove invitations
        $stmt = $this->prepare('DELETE FROM Invitation WHERE GroupID = ?');
        $stmt->bindValue(1, $this->groupID, SQLITE3_INTEGER);
        $this->finish($stmt);

        $stmt = $this->prepare('DELETE FROM Task WHERE GroupID = ?');
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

    public function changeUserLoad(LoadPair $pair): bool
    {
        $stmt = $this->prepare('UPDATE UserGroup SET Load = Load + ? WHERE GroupID = ? AND UserID = ?');
        $stmt->bindValue(1, $pair->Amount, SQLITE3_INTEGER);
        $stmt->bindValue(2, $this->groupID, SQLITE3_INTEGER);
        $stmt->bindValue(3, $pair->UserID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }
}

