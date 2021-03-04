<?php

require_once 'Database.php';

class Task implements IDBConvert, JsonSerializable
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
    public array $assigned;

    public static function fromRow(array $row) : Task
    {
        $task = new Task();

        $task->ID = $row['ID'] ?? -1;
        $task->GroupID = $row['GroupID'] ?? -1;
        $task->Name = $row['Name'] ?? "";
        $task->Colour = $row['Colour'] ?? "";
        $task->Desc = $row['Desc'] ?? "";
        $task->Completed = $row['Completed'] ?? -1;
        $task->CreationTime = $row['CreationTime'] ?? -1;
        $task->Deadline = $row['Deadline'] ?? -1;

        return $task;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Task|false
    {
        if(($row = $stmt->execute()->fetchArray()) == false) {
            $return = false;
        } else {
            $return = Task::fromRow($row);
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
            $return[0] = Task::fromRow($row);

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

class TaskDB extends Database
{
    private int $userID;

    function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // GET

    function getTask(int $taskID) : Task|false
    {
        $stmt = $this->prepare("SELECT Task.* FROM Task, 'Group', UserGroup WHERE Task.ID = :taskID AND UserGroup.AccountID = :userID");
        $stmt->bindValue(":taskID", $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Task::fetchSingle($stmt);
    }

    function getGroupsTasks(int $groupID) : array|false
    {
        $stmt = $this->prepare("SELECT Task.* FROM Task, 'Group', UserGroup WHERE Task.GroupID = :groupID AND UserGroup.AccountID = :userID");
        $stmt->bindValue(":groupID", $groupID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return Task::fetch($stmt);
    }

    function getAssigned(int $taskID) : array|false
    {
        return false;
    }

    // ------------------------------------------------------------------------
    // ADD

    function addTask(Task $task) : bool
    {
        $stmt = $this->prepare("INSERT INTO Task (ID, GroupID, Name, Colour, Desc, Completed, CreationTime, Deadline) VALUES (NULL, :groupID, :name, :colour, :desc, :complete, :creation, :deadline)");

        $stmt->bindValue(":groupID", $task->GroupID, SQLITE3_INTEGER);
        $stmt->bindValue(":name", $task->Name, SQLITE3_TEXT);
        $stmt->bindValue(":colour", $task->Colour, SQLITE3_TEXT);
        $stmt->bindValue(":desc", $task->Desc, SQLITE3_TEXT);
        $stmt->bindValue(":complete", $task->Completed, SQLITE3_INTEGER);
        $stmt->bindValue(":creation", $task->CreationTime, SQLITE3_INTEGER);
        $stmt->bindValue(":deadline", $task->Deadline, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    function removeTask(int $taskID) : bool
    {
        $stmt = $this->prepare("DELETE FROM Task WHERE ID = :taskID and groupID IN (SELECT GroupID FROM UserGroup WHERE AccountID = :userID)");
        $stmt->bindValue(":taskID", $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(":userID", $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}

