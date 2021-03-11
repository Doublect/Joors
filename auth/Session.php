<?php
require_once '../classes/Input.php';

class Session implements JsonSerializable
{
    public int $OwnerID;
    public string $SessionKey;

    public function __construct(int $OwnerID = 0, string $SessKey = '')
    {
        $this->OwnerID = $OwnerID;
        $this->SessionKey = $SessKey;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function jsonDeserialize($json): Session
    {
        $class = json_decode(urldecode($json));
        $sess = new Session();

        foreach ($class as $key => $value) {
            if ($value != null) {
                $sess->{Input::test_input($key)} = Input::test_input($value);
            }
        }

        return $sess;
    }
}

require_once '../classes/Database.php';

class SessionDB extends Database
{

    public function __construct()
    {
        parent::__construct();

        // Override database location
        $this->database = $this->getConnection();
    }

    private function getConnection(): SQLite3
    {
        return new SQLite3('../auth/session.db');
    }

    // ------------------------------------------------------------------------
    // CHECK

    public function checkSession(Session $sess): bool
    {
        // Try to get session owned by user
        $stmt = $this->prepare('SELECT * FROM Session WHERE OwnerID = ?');
        $stmt->bindValue(1, $sess->OwnerID, SQLITE3_INTEGER);
        $row = $stmt->execute()->fetchArray();

        // If user has no session, just return
        if ($row == null) {
            return false;
        }

        // If session is expired or the key is incorrect, then remove it
        if ($row['ExpiryTime'] < time() || $row['SessionKey'] != $sess->SessionKey) {
            $this->clearSessions($sess->OwnerID);
            return false;
        }

        // There is a valid session, update session expiration
        $stmt = $this->prepare('UPDATE Session SET ExpiryTime = ? WHERE OwnerID = ?');
        $stmt->bindValue(1, time() + 300, SQLITE3_INTEGER);
        $stmt->bindValue(2, $sess->OwnerID, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    // ------------------------------------------------------------------------
    // ADD

    public function createSession(int $userID): Session
    {
        // Make sure there is no session active
        $this->clearSessions($userID);

        $sess = new Session();

        // Generate session key
        $sess->OwnerID = $userID;
        $sess->SessionKey = md5(strval($userID) . strval(time()));

        // Add session to database
        $stmt = $this->prepare('INSERT INTO Session (ID, SessionKey, OwnerID, ExpiryTime) VALUES (NULL, ?, ?, ?)');
        $stmt->bindValue(1, $sess->SessionKey, SQLITE3_TEXT);
        $stmt->bindValue(2, $sess->OwnerID, SQLITE3_INTEGER);
        $stmt->bindValue(3, time() + 300, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();

        return $sess;
    }

    public function clearSessions(int $userID): bool
    {
        $stmt = $this->prepare('DELETE FROM Session WHERE OwnerID = ?');
        $stmt->bindValue(1, $userID, SQLITE3_INTEGER);
        $stmt->execute();

        return $this->finish($stmt);
    }
}