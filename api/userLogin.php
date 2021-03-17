<?php

// Check if variables are set
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Username']) && isset($_POST['Password'])) {
    require_once '../classes/User.php';
    require_once '../classes/Input.php';

    // Clean inputs
    $uname = Input::test_input($_POST['Username']);
    $pass = Input::test_input($_POST['Password']);

    // We don't have a userID
    $accdb = new UserDB(-1);

    // Check if user exists
    if(($user = $accdb->getUserByName($uname)) !== false) {
        // Check if password is correct
        if(password_verify($pass . $user->CreationTime, $user->Password)){
            require_once '../auth/Session.php';
            $sessDB = new SessionDB();

            // Don't send password data, create new session for user
            unset($user->Password);
            $sess = $sessDB->createSession($user->ID);

            // Return user and session data to client
            $data['User'] = $user;
            $data['Session'] = $sess;
            echo json_encode($data);
        } else {
            echo '2001';
        }
    } else {
        echo '2000';
    }

}