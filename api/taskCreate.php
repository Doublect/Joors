<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Task']) && isset($_POST['Assigned']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    require_once '../classes/Task.php';
    require_once '../classes/Input.php';
    $task = Task::jsonDeserialize($_POST['Task']);
    $assigned = (int)Input::test_input(json_decode($_POST['Assigned']));
    $taskDB = new TaskDB($sess->OwnerID);

    $taskDB->addTask($task);
    $task->ID = $taskDB->lastInsertRowID();


    require_once 'taskAssignment.php';
    $data['Task'] = $task;
    $data['Assigned'] = taskAssign('Add', $task, $assigned);

    echo json_encode($data);
}