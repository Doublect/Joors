<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {

    error_reporting(E_ALL);
    ini_set("display_errors",1);

    // Check if variables are set
    if(isset($_POST['User'])){
        require_once '../classes/User.php';

        // Create user object
        $user = new User();
        $user->jsonDeserialize($_POST['User']);
        $user->CreationTime = time();

        if(!isset($user->Email) || !isset($user->Password) || !isset($user->Username)) exit();

        // Sanitize and check email address validity
        $user->Email = filter_var($user->Email, FILTER_SANITIZE_EMAIL);
        if(!filter_var($user->Email, FILTER_VALIDATE_EMAIL)) exit("2004");

        // Ensure the username and password length
        if(strlen($user->Username) < 1 || strlen($user->Password <= 7)) exit();

        // Generate password hash
        $hash = password_hash(($user->Password . strval($user->CreationTime)), PASSWORD_DEFAULT);
        if($hash === false || $hash == null) exit();
        $user->Password = $hash;

        // We don't have a userID
        $userdb = new UserDB(-1);

        // Ensure user is unique
        if($userdb->usernameExists($user->Username)) exit("2003");
        if($userdb->emailExists($user->Email)) exit("2005");

        // Try to add user to database
        if($userdb->addUser($user)) {
            // If user was created return the userID
            require_once '../auth/Session.php';
            $sessDB = new SessionDB();

            // Get the new id of user and create a session
            $user->ID = $userdb->getUserByName($user->Username)->ID;
            $user->Password = "";
            $sess = $sessDB->createSession($user->ID);

            // Return user and session data to client
            $data["User"] = $user;
            $data["Session"] = $sess;
            echo json_encode($data);
        }
    }
}