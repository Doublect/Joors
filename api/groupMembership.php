<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Action']) && isset($_POST['Username']) && isset($_POST['GroupID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    // Create groupDB for groupEntity
    require_once '../classes/Group.php';
    require_once '../classes/User.php';

    $groupID = intval(Input::test_input($_POST['GroupID']));
    $action = Input::test_input($_POST['Action']);
    $Username = Input::test_input($_POST['Username']);

    $groupDB = new GroupDB($groupID);

    if(($user = (new UserDB(-1))->getUserByName($Username)) !== false && $user->ID !== $groupDB->getOwnerID()) {
        if($action === 'Add' && !($groupDB->isMember($user->ID))) {
            $groupDB->inviteUser($user->ID);
            echo json_encode($user);
        } elseif($action === 'Remove' && $groupDB->isMember($user->ID)) {
            $groupDB->removeMember($user->ID);
            echo json_encode($user);
        }
    }
}