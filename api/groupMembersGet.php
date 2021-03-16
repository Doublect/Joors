<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['GroupID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    // Create groupDB for groupEntity
    require_once '../classes/Group.php';
    $groupID = intval(Input::test_input($_POST['GroupID']));
    $groupDB = new GroupDB($groupID);

    if(($members = $groupDB->getMembers()) !== false) {

        $data['Members'] = $members;
        if(($invited = $groupDB->getInvited()) !== false) {
            $data['Invited'] = $invited;
        }

        echo json_encode($data);
    }

}