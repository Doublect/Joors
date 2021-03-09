<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['GroupID']) && isset($_POST['Session'])){
        require_once '../auth/Session.php';
        $sess = Session::jsonDeserialize($_POST['Session']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($sess)) {
            echo '2002';
            exit();
        }

        // Create groupDB for groupEntity
        require_once '../classes/Group.php';
        $groupID = intval(Input::test_input($_POST['GroupID']));
        $groupDB = new GroupDB($groupID);

        echo $groupDB->removeGroup($sess->OwnerID);
    }
}