<?php
class Session implements JsonSerializable {
    public int $OwnerID;
    public string $SessionKey;

    function __construct(int $OwnerID = 0, string $SessKey = "") {
        $this->OwnerID = $OwnerID;
        $this->SessionKey = $SessKey;
    }

    public function jsonSerialize() : array
    {
        return get_object_vars($this);
    }

    public static function jsonDeserialize($json) : Session{
        $class = json_decode($json);
        $sess = new Session();

        foreach ($class AS $key => $value) {
            if($value != null)
                $sess->{Input::test_input($key)} = Input::test_input($value);
        }

        return $sess;
    }
}

require_once "../classes/Database.php";
class SessionDB extends Database {

    function __construct()
    {
        parent::__construct();

        // Override database location
        $this->database = $this->getConnection();
    }

    private function getConnection() : SQLite3{
        return new SQLite3('../auth/session.db');
    }

    // ------------------------------------------------------------------------
    // CHECK

    function checkSession(Session $sess) : bool
    {
        // Try to get session owned by user
        $stmt = $this->prepare("SELECT * FROM Session WHERE OwnerID = :userID");
        $stmt->bindValue(":userID", $sess->OwnerID, SQLITE3_INTEGER);
        $arr = stmttoarr($stmt);    // stmttoarr returns false or a '2d array'

        // If user has no session, just return
        if(!$arr) {
            return false;
        }

        // If session is expired or the key is incorrect, then remove it
        if($arr[0]['ExpiryTime'] > time() || $arr[0]['SessionKey'] !== $sess->SessionKey){
            $this->clearSessions($sess->OwnerID);
            return false;
        }

        // There is a valid session, update session expiration
        $stmt = $this->prepare("UPDATE Session SET ExpiryTime = :time WHERE OwnerID = :userID");
        $stmt->bindValue(":userID", $sess->OwnerID, SQLITE3_INTEGER);
        $stmt->bindValue(":time", time() + 300, SQLITE3_INTEGER);
        return true;
    }

    // ------------------------------------------------------------------------
    // ADD


    function createSession(int $userID) : Session
    {
        // Make sure there is no session active
        $this->clearSessions($userID);

        $sess = new Session();

        // Generate session key
        $sess->OwnerID = $userID;
        $sess->SessionKey = md5(strval($userID) . strval(time()));

        // Add session to database
        $stmt = $this->prepare("INSERT INTO Session VALUES (NULL, :sessKey, :userID, :expiryTime)");
        $stmt->bindValue(":sessKey", $sess->SessionKey, SQLITE3_TEXT);
        $stmt->bindValue(":userID", $sess->OwnerID, SQLITE3_INTEGER);
        $stmt->bindValue(":time", time() + 300, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();

        return $sess;
    }

    function clearSessions(int $userID) : bool
    {
        $stmt = $this->prepare("DELETE FROM Session WHERE OwnerID = :userID");
        $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
        $stmt->execute();

        return $this->finish($stmt);
    }
}