<?php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['TaskID']) && isset($_POST['Session'])) {
    require_once '../auth/Session.php';
    $sess = Session::jsonDeserialize($_POST['Session']);

    // Check if session exists
    if(!(new SessionDB())->checkSession($sess)) {
        exit('2002');
    }

    require_once '../classes/Input.php';
    require_once '../classes/Task.php';

    $taskID = (int)Input::test_input($_POST['TaskID']);
    $taskDB = new TaskDB(-1);

    $taskDB->finishTask($taskID);
}