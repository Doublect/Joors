<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['TaskID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    // Create taskDB for user
    require_once '../classes/Task.php';
    $taskID = intval(Input::test_input($_POST['TaskID']));
    $taskDB = new TaskDB($sess->OwnerID);

    $taskDB->removeTask($taskID);
}