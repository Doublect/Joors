<?php

require_once 'Database.php';
require_once 'Allocator.php';

class Task implements IDBConvert, JsonSerializable
{
    public int $ID;
    public int $GroupID;
    public string $Name;
    public string $Desc;
    public string $Frequency;
    public int $FreqMult;
    public int $Length;
    public bool $Completed;
    public int $Next;

    public function getLoad(): int
    {
        return Task::freqConvert($this->Frequency) * $this->FreqMult * $this->Length;
    }

    public static function fromRow(array $row) : Task
    {
        $task = new Task();

        $task->ID = $row['ID'] ?? -1;
        $task->GroupID = $row['GroupID'] ?? -1;
        $task->Name = $row['Name'] ?? '';
        $task->Desc = $row['Desc'] ?? '';
        $task->Frequency = $row['Frequency'] ?? '';
        $task->FreqMult = $row['FreqMult'] ?? 1;
        $task->GroupID = $row['GroupID'] ?? -1;
        $task->Length = $row['Length'] ?? -1;
        $task->Completed = boolval($row['Completed']) ?? -1;
        $task->Next = $row['Next'] ?? -1;

        $task->FreqMult = min(12, max(1, $task->FreqMult));

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

    public static function checkNext(TaskDB $taskDB, Task|false $task): Task|false
    {
        if($task === false) {
            return false;
        }

        $time = time();

        if($task->Next < $time) {
            $freqtime = Task::freqTime($task->Frequency);
            $task->Next += ((($time - $task->Next) / $freqtime) + 1) * $freqtime;
            $task->Completed = false;
            $taskDB->updateNext($task->ID, $task->Next);
        }

        return $task;
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

    public static function freqConvert(string $frequency): int {
        switch ($frequency){
            case 'daily':
                return 365;
            case 'weekly':
                return 52;
            case 'monthly':
                return 12;
            case 'yearly':
                return 1;
            default:
                return 0;
        }
    }

    public static function freqTime(string $frequency): int {
        switch ($frequency){
            case 'daily':
                return 86400;
            case 'weekly':
                return 604800;
            case 'monthly':
                return 2592000;
            case 'yearly':
                return 31536000;
            default:
                return 0;
        }
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
    // CHECKS

    public function isAssigned(int $taskID, int $userID = null): bool
    {
        $userID = $userID ?? $this->userID;

        $stmt = $this->prepare('SELECT ID FROM Assigned WHERE TaskID = ? and UserID = ?');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return $this->exists($stmt);
    }

    // ------------------------------------------------------------------------
    // GET

    public function getTask(int $taskID): Task|false
    {
        $stmt = $this->prepare('SELECT Task.* FROM Task, UserGroup WHERE Task.ID = :taskID AND UserGroup.UserID = :userID');
        $stmt->bindValue(':taskID', $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        return Task::checkNext($this, Task::fetchSingle($stmt));
    }

    public function getGroupsTasks(int $groupID): array|false
    {
        $stmt = $this->prepare('SELECT Task.* FROM Task WHERE Task.GroupID = :groupID ORDER BY Next;');
        $stmt->bindValue(':groupID', $groupID, SQLITE3_INTEGER);
        $stmt->bindValue(':userID', $this->userID, SQLITE3_INTEGER);

        $res = Task::fetch($stmt);
        if($res === false) return false;

        foreach ($res as $task){
            Task::checkNext($this, $task);
        }

        return $res;
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

    public function assignTask(int $taskID, int $userID = null): bool
    {
        $userID = $userID ?? $this->userID;

        $stmt = $this->prepare('INSERT INTO Assigned (ID, TaskID, UserID) VALUES (NULL, ?, ?)');

        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // REMOVE

    /**
     * Handles removal of task from database and related entries. Updates the users loads to account for the removal.
     * @param int $taskID <p> A taskID existing in the database </p>
     * @return bool Returns the result of executing the final statement.
     */
    public function removeTask(int $taskID): bool
    {
        // Change the user's load to reflect the removal of the task
        $task = $this->getTask($taskID);
        foreach ($this->getAssigned($taskID) as $user) {
            unallocate($task, $user);
        }

        // Remove assigned
        $stmt = $this->prepare('DELETE FROM Assigned WHERE TaskID = ?');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $this->finish($stmt);

        // Remove task
        $stmt = $this->prepare('DELETE FROM Task WHERE ID = ? and groupID IN (SELECT GroupID FROM UserGroup WHERE UserID = ?)');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $this->userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    public function unassignTask(int $taskID, int $userID = null): bool
    {
        $userID = $userID ?? $this->userID;

        $stmt = $this->prepare('DELETE FROM Assigned WHERE TaskID = ? AND UserID = ?');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);
        $stmt->bindValue(2, $userID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }

    // ------------------------------------------------------------------------
    // UPDATE

    public function updateNext(int $taskID, int $time): bool
    {
        $stmt = $this->prepare('UPDATE Task SET Next = ?, Completed = 0 WHERE ID = ?');
        $stmt->bindValue(1, $time, SQLITE3_INTEGER);
        $stmt->bindValue(2, $taskID, SQLITE3_INTEGER);

        $res = $this->finish($stmt);

        //echo $res ? 'true' : 'false';
        return true;
    }

    public function finishTask(int $taskID): bool
    {
        $stmt = $this->prepare('UPDATE Task SET Completed = 1 WHERE ID = ?');
        $stmt->bindValue(1, $taskID, SQLITE3_INTEGER);

        return $this->finish($stmt);
    }
}

