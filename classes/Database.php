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

    function __construct()
    {
        $this->database = $this->getConnection();
    }

    function __destruct()
    {
        $this->database->close();
    }

    function exec($query)
    {
        $this->database->exec($query);
    }

    function exists(SQLite3Stmt $stmt) : bool
    {
        if($stmt->execute()->fetchArray() != false & $stmt->close()) return true;

        return false;
    }

    function finish(SQLite3Stmt $stmt) : bool
    {
        if($stmt->execute() != false & $stmt->close()) return true;

        return false;
    }

    function query(string $query) : SQLite3Result
    {
        return $this->database->query($query);
    }

    function querySingle(string $query) : SQLite3Result
    {
        return $this->database->querySingle($query,true);
    }

    function prepare(string $query) : SQLite3Stmt
    {
        return $this->database->prepare($query);
    }

    function escapeString(string $string) : string
    {
        return $this->database->escapeString($string);
    }

    private function getConnection() : SQLite3
    {
        return new SQLite3('../db/main.db');
    }
}

function stmttoarr(SQLite3Stmt $stmt) : array|false
{
    $res = $stmt->execute();

    if($res->numColumns() > 0) {
        $rows = array();

        for($i = 0; $row = $res->fetchArray(); $i++) {
            $rows[$i] = $row;
        }

        $stmt->close();
        return $rows;
    }

    return false;
}

function stmttojson(SQLite3Stmt $stmt) : string|false
{
    $arr = stmttoarr($stmt);

    if(!$arr) {
        return false;
    }

    return json_encode($arr);
}
