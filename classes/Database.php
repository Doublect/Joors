<?php

interface IDBConvert
{
    public static function fromRow(array $row) : object;
    public static function fetchSingle(SQLite3Stmt $stmt) : object|false;
    public static function fetch(SQLite3Stmt $stmt) : array|false;
}

class Database
{
    protected SQLite3 $database;

    public function __construct()
    {
        $this->database = $this->getConnection();
    }

    public function __destruct()
    {
        $this->database->close();
    }

    public function exec($query)
    {
        $this->database->exec($query);
    }

    public function exists(SQLite3Stmt $stmt): bool
    {
        if(($stmt->execute()->fetchArray()) == false) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    public function finish(SQLite3Stmt $stmt): bool
    {
        if (!$stmt->execute() & $stmt->close()) {
            return true;
        }

        return false;
    }

    public function query(string $query): SQLite3Result
    {
        return $this->database->query($query);
    }

    public function querySingle(string $query): SQLite3Result
    {
        return $this->database->querySingle($query, true);
    }

    public function prepare(string $query): SQLite3Stmt
    {
        return $this->database->prepare($query);
    }

    public function escapeString(string $string): string
    {
        return $this->database->escapeString($string);
    }

    private function getConnection(): SQLite3
    {
        return new SQLite3('../db/main.db');
    }
}