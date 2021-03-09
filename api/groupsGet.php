<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    // Create userDB for user
    require_once '../classes/User.php';
    $userDB = new UserDB($sess->OwnerID);

    if(($groups = $userDB->getUsersGroups()) !== false) {
        $data['Member'] = $groups;

        if(($invitations = $userDB->getInvitations()) !== false) {
            $data['Invited'] = $invitations;
        }

        echo json_encode($data);
    }
}