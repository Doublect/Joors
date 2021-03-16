<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Action']) && isset($_POST['Task']) && isset($_POST['TargetID']) && isset($_POST['Session'])) {

    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    require_once '../classes/Input.php';
    require_once '../classes/Task.php';

    $target = (int)Input::test_input($_POST['TargetID']);
    $task = Task::jsonDeserialize($_POST['Task']);
    $action = (string)Input::test_input($_POST['Action']);

    $res = taskAssign($action, $task, $target);

    if($res) {
        echo json_encode($res);
    }
}

function taskAssign($action, $task, $targetID): int|null {

    require_once '../classes/Group.php';
    require_once '../classes/Allocator.php';

    if($targetID == -2) {
        $userID = autoAssign($task->ID, $task->GroupID);
        if($userID && $action == 'Add') {
            allocate($task, (array)$userID);
            return $userID;
        }
    } elseif($targetID != -1 && (new GroupDB($targetID))->isMember($targetID)) {
        if($action = 'Add') {
            allocate($task, (array)$targetID);
        } elseif($action = 'Remove') {
            unallocate($task, $targetID);
        }
        return $targetID;
    }

    return null;
}
