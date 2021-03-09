<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Task']) && isset($_POST['Assigned']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    require_once '../classes/Task.php';
    $task = Task::jsonDeserialize($_POST['Task']);
    $taskDB = new TaskDB($sess->OwnerID);

    $taskDB->addTask($task);
}