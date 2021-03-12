<?php
require_once "Task.php";
require_once "Group.php";

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
    return 0;
}

/**
 * Rebalances the load of assigned users of a task, when new users are assigned.
 * @param Task $task <p>
 * A task which has been added to the database.
 * </p>
 * <p>
 * Must have an ID and GroupID set.
 * </p>
 * @param array $userIDs <p>
 * An array of integers. The IDs of users being assigned to the task.
 * </p>
 */
function allocate(Task $task, array $userIDs): void {
    // Instantiate databases
    $groupDB = new GroupDB($task->GroupID);
    $taskDB = new TaskDB(-1);

    $assigned = $taskDB->getAssigned($task->ID);
    $assignedNum = count($assigned);
    $newAssignedNum = $assignedNum + count($userIDs);

    foreach($assigned as $user) {
        $load = new LoadPair($user, (int)($task->Length / $newAssignedNum) - (int)($task->Length / $assignedNum));

        $groupDB->changeUserLoad($load);
    }

    foreach($userIDs as $user) {
        $load = new LoadPair($user, (int)($task->Length / $newAssignedNum));

        $groupDB->changeUserLoad($load);
        $taskDB->assignTask($task->ID, $user);
    }
}

/**
 * Rebalances the load of assigned users of a task, when a user is unassigned.
 * @param Task $task <p>
 * A task which has been added to the database.
 * </p>
 * <p>
 * Must have an ID and GroupID set.
 * </p>
 * @param int $userID <p>
 * The ID of the user being unassigned from the task.
 * </p>
 */
function unallocate(Task $task, int $userID): void {
    $groupDB = new GroupDB($task->GroupID);
    $taskDB = new TaskDB($userID);

    $assigned = $taskDB->getAssigned($task->ID);
    $assignedNum = count($assigned);

    if($assignedNum > 1) {
        $newAssignedNum = $assignedNum - 1;

        foreach ($assigned as $user) {
            $load = new LoadPair($user, (int)($task->Length / $newAssignedNum) - (int)($task->Length / $assignedNum));

            $groupDB->changeUserLoad($load);
        }
    }

    $load = new LoadPair($userID, -$task->Length);

    $groupDB->changeUserLoad($load);
    $taskDB->unassignTask($task->ID);
}

/**
 * Finds the unassigned user with the least amount of work assigned to them.
 * @param Task $task <p>
 * A task which has been added to the database.
 * </p>
 * <p>
 * Must have an ID and GroupID set.
 * </p>
 * @return int|false Return a UserID on success,
 * or false on failure
 */
function autoAssign(int $taskID, int $groupID): int|false
{
    $groupDB = new GroupDB($groupID);
    $taskDB = new TaskDB(-1);
    $assignedSet = array();

    foreach($taskDB->getAssigned($taskID) as $assigned) {
        $assignedSet[intval($assigned)] = true;
    }

    $minLoad = new LoadPair(-1,PHP_INT_MAX);

    foreach ($groupDB->getUserLoad() as $load) {
        if(!isset($assignedSet[$load->UserID]) && $load->Amount < $minLoad->Amount) {
            $minLoad = $load;
        }
    }

    return ($minLoad->UserID != -1) ? $minLoad->UserID : false;
}