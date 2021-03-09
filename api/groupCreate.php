<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Name']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    // Create groupDB for groupEntity
    require_once '../classes/Group.php';
    $name = strval(Input::test_input($_POST['Name']));

    if(strlen($name) > 0) {
        $groupDB = new GroupDB(-1);
        $groupDB->addGroup($name, $sess->OwnerID);
    }
}