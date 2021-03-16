<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['taskID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    // Create taskDB for user
    require_once '../classes/Task.php';
    $taskID = intval(Input::test_input($_POST['taskID']));
    $taskDB = new TaskDB($sess->OwnerID);

    if(($task = $taskDB->getTask($taskID)) !== false) {
        echo json_encode($task);
    }
}