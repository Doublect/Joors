<?php

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Action']) && isset($_POST['Task']) && isset($_POST['TargetID']) && isset($_POST['Session'])) {

    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        echo '2002';
        exit();
    }

    require_once '../classes/Input.php';
    require_once '../classes/Group.php';
    require_once '../classes/User.php';
    require_once '../classes/Allocator.php';

    $target = (int)Input::test_input($_POST['TargetID']);
    $task = Task::jsonDeserialize($_POST['Task']);
    $action = (string)Input::test_input($_POST['Action']);
    $taskDB = new TaskDB((int)Input::test_input($task->ID));

    if($target == -2) {
        $userID = autoAssign($task->ID, $task->GroupID);
        if($userID && $action == 'Add') {
            $taskDB->assignTask($task->ID);
            allocate($task, (array)$userID);
            exit('OK');
        }
    }

    if($target == -1) {
        exit('OK');
    }

    if((new GroupDB($target))->isMember($target)) {
        if($action = 'Add' && $taskDB->isAssigned($task->ID)) {
            $taskDB->assignTask($task->ID);
            allocate($task, (array)$target);
            exit('OK');
        } elseif($action = 'Remove' && !$taskDB->isAssigned($task->ID) ) {
            $taskDB->unassignTask($task->ID);
            unallocate($task, $target);
            exit('OK');
        }
    }
}
