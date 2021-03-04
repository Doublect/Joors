<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['GroupID']) && isset($_POST['Session'])){

        $sess = Session::jsonDeserialize($_POST['Session']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($sess)) {
            echo "2002";
            exit();
        }

        // Create choreDB for user
        require_once "../classes/Task.php";
        $groupID = intval(Input::test_input($_POST['GroupID']));
        $choreDB = new TaskDB($sess->OwnerID);

        if(($chores = $choreDB->getGroupsTasks($groupID)) !== false) {
            echo json_encode($chores);
        }
    }
}