<?php
require_once "Task.php";

class Allocator
{

}


function freqMult(string $frequency): int {
    switch ($frequency){
        case 'daily':
            return 365;
        case 'weekly':
            return 52;
        case 'monthly':
            return 12;
        case 'yearly':
            return 1;

    }
}

function allocate(array $tasks, int $groupID) {
    $groupDB = new GroupDB($groupID);

    $members = $groupDB->getMembers();

    $burden = array();

    for($i = 0; $i < count($members); $i++){
        $userDB = new userDB($members[$i]);

        $assigned = $userDB->get;
    }
}