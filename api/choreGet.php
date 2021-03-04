<?php

require_once "../auth/Session.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['ChoreID']) && isset($_POST['UserID']) && isset($_POST['SessionKey'])){

        $userID = intval(Input::test_input($_POST['UserID']));
        $sessKey = Input::test_input($_POST['SessionKey']);

        // Check if session exists
        if(!(new SessionDB())->checkSession($userID, $sessKey)) {
            echo "2002";
            exit();
        }

        // Create choreDB for user
        require_once "../classes/Chore.php";
        $choreID = intval(Input::test_input($_POST['ChoreID']));
        $choreDB = new ChoreDB($userID);

        if(($chore = $choreDB->getChore($choreID)) !== false) {
            echo json_encode($chore);
        }
    }
}