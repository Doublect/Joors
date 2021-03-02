<?php
class Session {
    public int $OwnerID;
    public string $SessionKey;

    function __construct(int $OwnerID = 0, string $SessKey = "") {
        $this->OwnerID = $OwnerID;
        $this->SessionKey = $SessKey;
    }
}

class SessionDatabase{
    private SQLite3 $database;

    function __construct(){
        $this->database = $this->getConnection();
    }

    function prepare(string $query) : SQLite3Stmt{
        return $this->database->prepare($query);
    }

    private function getConnection() : SQLite3{
        return new SQLite3('session.db');
    }
}

function checkSession(int $userID, string $sessKey) : bool {
    $db = new SessionDatabase();

    // Try to get session owned by user
    $stmt = $db->prepare("SELECT * FROM Session WHERE OwnerID = :userID");
    $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
    $arr = stmttoarr($stmt);    // stmttoarr returns false or a '2d array'

    // If user has no session, just return
    if(!$arr) {
        return false;
    }

    // If session is expired or the key is incorrect, then remove it
    if($arr[0]['ExpiryTime'] > time() || $arr[0]['SessionKey'] !== $sessKey){
        $stmt = $db->prepare("DELETE FROM Session WHERE OwnerID = :userID");
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();

        return false;
    }

    // There is a valid session, update session expiration
    $stmt = $db->prepare("UPDATE Session SET ExpiryTime = :time WHERE OwnerID = :userID");
    $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
    $stmt->bindValue(":time", time() + 300, SQLITE3_INTEGER);
    return true;
}

function createSession(int $userID) : Session{
    $sess = new Session();
    $db = new Database();

    // Generate session key
    $sess->OwnerID = $userID;
    $sess->SessionKey = md5(strval($userID) . strval(time()));

    // Add session to database
    $stmt = $db->prepare("INSERT INTO Session VALUES (NULL, :sessKey, :userID, :expiryTime)");
    $stmt->bindValue(":sessKey", $sess->SessionKey, SQLITE3_TEXT);
    $stmt->bindValue(":userID", $sess->OwnerID, SQLITE3_INTEGER);
    $stmt->bindValue(":time", time() + 300, SQLITE3_INTEGER);
    $stmt->execute();
    $stmt->close();

    return $sess;
}