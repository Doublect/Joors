<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['UserID']) && isset($_POST['SessionKey'])){

        $userID = intval(Input::test_input($_POST['UserID']));
        $sessKey = Input::test_input($_POST['SessionKey']);

        // Check if session exists
        if(!checkSession($userID, $sessKey)) {
            echo "2002";
            exit();
        }

        // Create userDB for user
        require_once "../classes/Account.php";
        $userDB = new AccountDB($userID);

        if(($groups = $userDB->getUsersGroups()) !== false) {
            echo json_encode($groups);
        }
    }
}