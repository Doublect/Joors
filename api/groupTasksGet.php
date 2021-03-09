<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['GroupID']) && isset($_POST['Session'])){

        require_once "../auth/Session.php";

        // Get the session object
        $sess = Session::jsonDeserialize($_POST['Session']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($sess)) {
            echo "2002";
            exit();
        }

        // Create taskDB for user
        require_once "../classes/Task.php";
        $groupID = intval(Input::test_input($_POST['GroupID']));
        $taskDB = new TaskDB($sess->OwnerID);

        // Query the database and return result
        if(($tasks = $taskDB->getGroupsTasks($groupID)) !== false) {
            for($i = 0; $i < count($tasks); $i++){
                $tasks[$i]->Assigned = $taskDB->getAssigned($tasks[$i]->ID);
            }

            echo json_encode($tasks);
        } else {
            echo "3001";
        }
    }
}