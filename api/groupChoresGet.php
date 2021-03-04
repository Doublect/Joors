<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['GroupID']) && isset($_POST['UserID']) && isset($_POST['SessionKey'])){

        $userID = intval(Input::test_input($_POST['UserID']));
        $sessKey = Input::test_input($_POST['SessionKey']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($userID, $sessKey)) {
            echo "2002";
            exit();
        }

        // Create choreDB for user
        require_once "../classes/Chore.php";
        $groupID = intval(Input::test_input($_POST['GroupID']));
        $choreDB = new ChoreDB($userID);

        if(($chores = $choreDB->getGroupsChores($groupID)) !== false) {
            echo json_encode($chores);
        }
    }
}