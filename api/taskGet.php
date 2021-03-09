<?php

require_once '../auth/Session.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['taskID']) && isset($_POST['Session'])) {
   $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    // Create taskDB for user
    require_once '../classes/Task.php';
    $taskID = intval(Input::test_input($_POST['taskID']));
    $taskDB = new TaskDB($sess->SessionKey);

    if(($task = $taskDB->getTask($taskID)) !== false) {
        echo json_encode($task);
    }

}