<?php
require_once 'Task.php';
require_once 'Group.php';


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
    // Create databases
    $taskDB = new TaskDB(-1);
    $groupDB = new GroupDB($task->GroupID);

    // Array of users assigned to task
    $assigned = $taskDB->getAssigned($task->ID);
    $assignedCnt = count($assigned);
    $newAssignedCnt = $assignedCnt + count($userIDs);

    $taskLoad = $task->getLoad();

    // Loop over all assigned users, to reduce the load assigned to them
    foreach($assigned as $user) {
        $userLoad = new LoadPair($user, (int)($taskLoad / $newAssignedCnt) - (int)($taskLoad / $assignedCnt));

        $groupDB->changeUserLoad($userLoad);
    }

    // Assign the remaining load to the new users and assign them to the task
    foreach($userIDs as $user) {
        $userLoad = new LoadPair($user, (int)($taskLoad / $newAssignedCnt));

        $groupDB->changeUserLoad($userLoad);
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

    // Create databases and make sure user is assigned to task
    $taskDB = new TaskDB($userID);
    $groupDB = new GroupDB($task->GroupID);
    if(!$taskDB->isAssigned($task->ID, $userID)) return;

    $assigned = $taskDB->getAssigned($task->ID);
    $assignedNum = count($assigned);

    $taskLoad = $task->getLoad();

    if($assignedNum > 1) {
        $newAssignedNum = $assignedNum - 1;

        foreach ($assigned as $user) {
            $userLoad = new LoadPair($user, (int)($taskLoad / $newAssignedNum) - (int)($taskLoad / $assignedNum));

            $groupDB->changeUserLoad($userLoad);
        }
    }

    $userLoad = new LoadPair($userID, -$taskLoad);

    $groupDB->changeUserLoad($userLoad);
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
function autoAssign(Task $task): int|false
{
    $groupDB = new GroupDB($task->GroupID);
    $taskDB = new TaskDB(-1);
    $assignedSet = array();

    foreach($taskDB->getAssigned($task->ID) as $assigned) {
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