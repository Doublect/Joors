<?php

require_once "../auth/Session.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['choreID']) && isset($_POST['userID']) && isset($_POST['sessionKey'])){

        $userID = Input::test_input($_POST['userID']);
        $sessKey = Input::test_input($_POST['sessionKey']);

        // Check if session exists
        if(!checkSession($userID, $sessKey)) exit();

        $choreID = $_POST['choreID'];


    }
}