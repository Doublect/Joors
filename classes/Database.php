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
        $res = boolval($stmt->execute()->fetchArray());
        $stmt->close();
        return $res;
    }

    public function finish(SQLite3Stmt $stmt): bool
    {
        $res = boolval($stmt->execute());
        $stmt->close();
        return $res;
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

    public function lastInsertRowID(): int {
        return $this->database->lastInsertRowID();
    }

    private function getConnection(): SQLite3
    {
        return new SQLite3('../db/main.db');
    }
}