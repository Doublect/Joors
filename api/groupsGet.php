<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['Session'])){
        require_once '../auth/Session.php';
        $sess = Session::jsonDeserialize($_POST['Session']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($sess)) {
            echo '2002';
            exit();
        }

        // Create userDB for user
        require_once '../classes/User.php';
        $userDB = new UserDB($sess->OwnerID);

        if(($groups = $userDB->getUsersGroups()) !== false) {
            echo json_encode($groups);
        }
    }
}