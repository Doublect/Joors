<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Action']) && isset($_POST['GroupID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    // Create groupDB for groupEntity
    require_once '../classes/Group.php';
    require_once '../classes/User.php';

    $groupID = intval(Input::test_input($_POST['GroupID']));
    $action = Input::test_input($_POST['Action']);

    $groupDB = new GroupDB($groupID);

    if(($user = (new UserDB($sess->OwnerID))->getUser()) !== false) {
        if($action === 'Add') {
            $groupDB->addMember($user->ID);
            $groupDB->removeInvitation($user->ID);
        } elseif($action === 'Remove') {
            $groupDB->removeInvitation($user->ID);
        }
    }
}