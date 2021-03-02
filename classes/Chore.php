<?php


class Chore
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
}

class ChoreDB
{
    private Database $db;
    private int $userID;

    function __construct($userid)
    {
        $this->db = new Database();
        $this->userID = $userid;
    }

    // ------------------------------------------------------------------------
    // GET

    function getChore(int $choreID) : SQLite3Stmt
    {
        $stmt = $this->db->prepare("SELECT Chore.* FROM Chore, 'Group', AccountGroup WHERE Chore.ID = :choreID AND AccountGroup.AccountID = :userID");
        $stmt->bindValue(":choreID", $choreID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $stmt;
    }

    function getGroupChores(int $groupID) : SQLite3Stmt
    {
        $stmt = $this->db->prepare("SELECT Chore.* FROM Chore, 'Group', AccountGroup WHERE Chore.GroupID = :groupID AND AccountGroup.AccountID = :userID");
        $stmt->bindValue(":groupID", $groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $stmt;
    }

    // ------------------------------------------------------------------------
    // ADD

    function addChore(Chore $chore) : bool
    {
        $stmt = $this->db->prepare("INSERT INTO Chore VALUES (NULL, :groupID, :assignID, :name, :colour, :desc, :complete, :creation, :deadline)");

        $stmt->bindValue(":groupID", $chore->GroupID, SQLITE3_INTEGER);
        $stmt->bindValue(":assignID", $chore->AssignID, SQLITE3_INTEGER);
        $stmt->bindValue(":name", $chore->Name, SQLITE3_TEXT);
        $stmt->bindValue(":colour", $chore->Colour, SQLITE3_TEXT);
        $stmt->bindValue(":desc", $chore->Desc, SQLITE3_TEXT);
        $stmt->bindValue(":complete", $chore->Completed, SQLITE3_INTEGER);
        $stmt->bindValue(":creation", $chore->CreationTime, SQLITE3_INTEGER);
        $stmt->bindValue(":deadline", $chore->Deadline, SQLITE3_INTEGER);

        return $this->db->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeChore(int $choreID) : bool
    {
        $stmt = $this->db->prepare("DELETE FROM Chore WHERE ID = :choreID and groupID IN (SELECT GroupID FROM AccountGroup WHERE AccountID = :userID)");
        $stmt->bindValue(":choreID", $choreID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $this->db->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}

