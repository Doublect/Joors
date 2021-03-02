<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if variables are set
    if(isset($_POST['Username']) && isset($_POST['Password'])){
        require_once "../classes/Account.php";

        // Create user object
        $user = new Account();
        $user->Username = Input::test_input($_POST['Username']);
        $user->Password = Input::test_input($_POST['Password']);
        $user->CreationTime = Input::test_input(time());

        // Ensure that the password length
        if(strlen($user->Password <= 7)) exit();

        // Generate password hash
        $hash = password_hash(($user->Password . strval($user->CreationTime)), PASSWORD_DEFAULT);
        if($hash === false || $hash == null) exit();
        $user->Password = $hash;

        // We don't have a userID
        $accdb = new AccountDB(-1);

        // Ensure user is unique
        if(!$accdb->uniqueCheck($user)) exit();

        // Try to add user to database
        if($accdb->addUser($user)) {
            // If user was created return the userID
            require_once '../auth/Session.php';

            // Get the new id of user and create a session
            $user->ID = $accdb->getUserByName($user->Username)->ID;
            $sess = createSession($user->ID);

            // Return user and session data to client
            echo json_encode($user) . json_encode($sess);
        }
    }
}