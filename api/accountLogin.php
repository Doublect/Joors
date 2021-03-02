<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if variables are set
    if(isset($_POST['Username']) && isset($_POST['Password'])){
        require_once "../classes/Account.php";

        // Clean inputs
        $uname = Input::test_input($_POST['Username']);
        $pass = Input::test_input($_POST['Password']);

        // We don't have a userID
        $accdb = new AccountDB(-1);

        // Check if user exists
        if(($user = $accdb->getUserByName($uname)) !== false) {
            // Check if password is correct
            if(password_verify($pass . $user->CreationTime, $user->Password)){

                // Don't send password data, create new session for user
                $user->Password = "";
                $sess = createSession($user->ID);

                // Return user and session data to client
                echo json_encode($user) . json_encode($sess);
            } else {
                echo "2001";
                exit();
            }
        } else {
            echo "2000";
            exit();
        }
    }
}