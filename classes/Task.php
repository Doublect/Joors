<?php

require_once 'Database.php';

class Task implements IDBConvert, JsonSerializable
{
    public int $ID;
    public int $GroupID;
    public string $Name;
    public string $Desc;
    public string $Frequency;
    public int $Length;
    public bool $Completed;
    public int $Next;

    public static function fromRow(array $row) : Task
    {
        $task = new Task();

        $task->ID = $row['ID'] ?? -1;
        $task->GroupID = $row['GroupID'] ?? -1;
        $task->Name = $row['Name'] ?? '';
        $task->Desc = $row['Desc'] ?? '';
        $task->Frequency = $row['Frequency'] ?? '';
        $task->Length = $row['Length'] ?? -1;
        $task->Completed = $row['Completed'] ?? -1;
        $task->Next = $row['Next'] ?? -1;

        return $task;
    }

    public static function fetchSingle(SQLite3Stmt $stmt) : Task|false
    {
        if(!($row = $stmt->execute()->fetchArray())) {
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

        if(!($row = $res->fetchArray())) {
            $return = false;
        } else {
            $return = array();
            $return[0] = Task::fromRow($row);

            for($i = 1; ($row = $res->fetchArray()); $i++) {
                $return[$i] = Task::fromRow($row);
            }
        }

        $stmt->close();
        return $return;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public static function jsonDeserialize($json) : Task {
        $class = json_decode($json);
        $task = new Task();

        foreach ($class AS $key => $value) {
            if($value !== null) {
                $task->{Input::test_input($key)} = Input::test_input($value);
            }
        }

        return $task;
    }
}

class TaskDB extends Database
{
    private int $userID;

    public function __construct($userid)
    {
        $this->userID = $userid;
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // GET

    public function getTask(int $taskID): Task|false
    {
        $stmt = $this->prepare('SELECT Task.* FROM Task, UserGroup WHERE Task.ID = :taskID AND UserGroup.UserID = :userID');
        $stmt->bindValue(':taskID', $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        return Task::fetchSingle($stmt);
    }

    public function getGroupsTasks(int $groupID): array|false
    {
        $stmt = $this->prepare('SELECT Task.* FROM Task WHERE Task.GroupID = :groupID');
        $stmt->bindValue(':groupID', $groupID, SQLITE3_INTEGER);
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        return Task::fetch($stmt);
    }

    public function getAssigned(int $taskID): array|false
    {
        $stmt = $this->prepare('SELECT Assigned.UserID FROM Assigned WHERE Assigned.TaskID = :taskID');
        $stmt->bindValue(':taskID', $taskID, SQLITE3_INTEGER);

        $res = $stmt->execute();
        $users = array();

        for ($i = 0; ($row = $res->fetchArray()); $i++) {
            $users[$i] = $row['UserID'];
        }
        return $users;
    }

    // ------------------------------------------------------------------------
    // ADD

    public function addTask(Task $task): bool
    {
        $stmt = $this->prepare('INSERT INTO Task (ID, GroupID, Name, Desc, Frequency, Length, Completed, Next) VALUES (NULL, :groupID, :name, :desc, :freq, :length, :complete, :next)');

        $stmt->bindValue(':groupID', $task->GroupID, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $task->Name, SQLITE3_TEXT);
        $stmt->bindValue(':desc', $task->Desc, SQLITE3_TEXT);
        $stmt->bindValue(':freq', $task->Frequency, SQLITE3_TEXT);
        $stmt->bindValue(':length', $task->Completed, SQLITE3_INTEGER);
        $stmt->bindValue(':complete', $task->Completed, SQLITE3_INTEGER);
        $stmt->bindValue(':next', $task->Next, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    public function removeTask(int $taskID): bool
    {
        $stmt = $this->prepare('DELETE FROM Task WHERE ID = ? and groupID IN (SELECT GroupID FROM UserGroup WHERE UserID = ?)');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE
}

