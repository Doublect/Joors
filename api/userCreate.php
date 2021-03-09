<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['User'])) {

    require_once '../classes/User.php';

    // Create user object
    $user = User::jsonDeserialize($_POST['User']);
    $user->CreationTime = time();

    if(!isset($user->Email) || !isset($user->Password) || !isset($user->Name)) exit('1999');

    // Sanitize and check email address validity
    $user->Email = filter_var($user->Email, FILTER_SANITIZE_EMAIL);
    if(!filter_var($user->Email, FILTER_VALIDATE_EMAIL)) exit('2004');


    // Ensure the username and password length
    if(strlen($user->Name) < 1 || strlen($user->Password <= 7)) exit();

    // Generate password hash
    $hash = password_hash(($user->Password . strval($user->CreationTime)), PASSWORD_DEFAULT);
    if($hash === false || $hash == null) exit();
    $user->Password = $hash;

    // We don't have a userID
    $userDB = new UserDB(-1);

    // Ensure user is unique
    if($userDB->usernameExists($user->Name)) exit('2003');
    if($userDB->emailExists($user->Email)) exit('2005');

    // Try to add user to database
    if($userDB->addUser($user)) {
        // If user was created return the userID
        require_once '../auth/Session.php';
        $sessDB = new SessionDB();

        // Get the new id of user and create a session
        $user->ID = $userDB->getUserByName($user->Name)->ID;
        $user->Password = '';
        $sess = $sessDB->createSession($user->ID);

        // Return user and session data to client
        $data['User'] = $user;
        $data['Session'] = $sess;
        echo json_encode($data);
    }
}