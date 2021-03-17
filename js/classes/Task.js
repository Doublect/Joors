import {users} from "../home.js";
import * as Library from "./Library.js";

export default class Task {
    constructor(ID, GroupID, Name, Desc, Frequency, FreqMult, Length, Completed, CreationTime, Next, Assigned) {
        this.ID = ID;
        this.GroupID = GroupID;
        this.Name = Name;
        this.Desc = Desc;
        this.Frequency = Frequency;
        this.FreqMult = FreqMult;
        this.Length = Length;
        this.Completed = Completed;
        this.Next = Next;
        this.Assigned = Assigned;
    }
}

/**
 * Creates task elements and places them under an element, depending on completion.
 * @param {GroupEntity} groupEntity The group which owns the task.
 * @param {Task} task
 */
export function createTaskElement(groupEntity, task)
{
    let tasksdiv; // The parent
    let taskElem; // The element

    if(task.Completed === true) {
        tasksdiv = $("#content").find("#tasksfin");
        taskElem = $($.parseHTML(taskFinishedSnippet));
    } else {
        tasksdiv = $("#content").find("#tasks");
        taskElem = $($.parseHTML(taskSnippet));

        // Functionality for task finish button
        taskElem.find("#task-finish").on("click", function () {
            if ($(this).hasClass("red")) {
                let button = $(this);
                $.post("api/taskFinish.php", { TaskID : task.ID, Session : localStorage.getItem("Session") }, function (){
                    $("#content").find("#tasksfin").append(taskElem);
                    taskElem.find("#deadlinetext").text("Just finished!");
                    button.remove();
                });
            } else {
                $(this).removeClass("dgrey");
                $(this).addClass("red");
                $(this).text("Are you sure?");
            }
        });
    }

    taskElem.find("#title").text(task.Name);
    taskElem.find("#description").text(task.Desc);

    // Capitalize frequency
    taskElem.find("#frequency").text(task.Frequency.charAt(0).toUpperCase() + task.Frequency.slice(1));
    taskElem.find("#freqmult").text(task.FreqMult);

    // Interpret and display date
    let dateobj = new Date(task.Next * 1000);
    const year = dateobj.getFullYear();
    const month = dateobj.getMonth() + 1;
    const day = dateobj.getDate();
    const hour = dateobj.getHours();
    let minutes = dateobj.getMinutes();
    if(minutes < 10) minutes = "0" + minutes.toString();
    taskElem.find("#nextdeadline").text(year + "/" + month + "/" + day + " " + hour + ":" + minutes);

    // Displays the users assigned to task
    let assignelem = taskElem.find("#assigned");
    if(task.Assigned) {
        for(let id of task.Assigned){
            let p = $("<p class='inline-element' id='" + id + "'></p>").text(users[id].Name);

            assignelem.append(p);
        }
    }

    // Add users to assignment dropdown
    let assigned = taskElem.find("#assignedselect");
    groupEntity.Members.forEach(function (user) {
        Library.addUserAsOption(user, assigned);
    });

    // Subscribe to user membership changes
    assignUserEvent(assigned[0]);

    // Logic for handling assignment changes
    taskElem.find("#task-assign").on("click", function (){
        let selected = assigned.children(":selected").val();
        if(selected ===  '-2' || !task.Assigned.has(parseInt(selected))) { // If auto-assign or unassigned, then add to assigned
            $.post('api/taskAssignment.php', { Task : JSON.stringify(task), TargetID: selected, Action: "Add", Session : localStorage.getItem("Session")}, function (data) {
                switch (data) {
                    case "2002":
                        Library.LogOut();
                        break;
                    case "":
                        break;
                    default:
                        data = JSON.parse(data);

                        // Update variable and display
                        task.Assigned.add(data);
                        assignelem.append($("<p class='inline-element' id='" + data + "'></p>").text(users[data].Name));
                }
            });
        } else { // Otherwise, remove from assigned
            $.post('api/taskAssignment.php', { Task : JSON.stringify(task), TargetID: selected, Action: "Remove", Session : localStorage.getItem("Session")}, function (data) {
                switch (data) {
                    case "2002":
                        Library.LogOut();
                        break;
                    case "":
                        break;
                    default:
                        data = JSON.parse(data);

                        // Update variable and display
                        task.Assigned.delete(data);
                        assignelem.children().remove("#" + data);
                }
            });
        }
    });

    // Logic for delete button
    taskElem.find("#task-delete").on("click", function () {
        if ($(this).hasClass("red")) {
            $.post("api/taskDelete.php", { TaskID : task.ID, Session : localStorage.getItem("Session") }, function (){
                taskElem.remove();
            });
        } else {
            $(this).removeClass("dgrey");
            $(this).addClass("red");
            $(this).text("Are you sure?");
        }
    });

    // Add to parent
    tasksdiv.append(taskElem);
}

/**
 * Create form to handle creation of new tasks.
 * @param groupEntity The group for which to create the element for.
 */
export function createTaskForm(groupEntity)
{
    let formElem = $($.parseHTML(formSnippet));

    // Set default value of date field
    let date = formElem.find("#date");
    date[0].valueAsDate = new Date();
    date[0].min = new Date().toISOString().split("T")[0];

    // Add users to assignment dropdown
    let assigned = formElem.find("#assignedselect");
    groupEntity.Members.forEach(function (user) {
        Library.addUserAsOption(user, assigned);
    });

    // Subscribe to user membership changes
    assignUserEvent(assigned[0]);

    // Handle form submission
    formElem.find("#submit").on("click", function (event){
        event.preventDefault();
        parseNewTaskForm(formElem, groupEntity);
    });

    // Add to parent
    $("#content").find("#taskcreate").append(formElem);
}

/**
 * Checks form's input fields and calls 'taskCreate.php'.
 */
export function parseNewTaskForm(form, groupEntity)
{
    let task = new Task;

    // Make sure the title is set
    task.Name = form.find("#title").val();
    if(task.Name === "") {
        return;
    }

    task.GroupID = groupEntity.Group.ID;

    // Read in fields
    task.Desc = form.find("#description").val();
    task.Length = form.find("#length").val();
    task.FreqMult = form.find("#freqmult").val();
    task.Completed = 0;

    // Get the selected elements from the drop-downs
    let assigned = form.find("#assignedselect").children(":selected").val();
    task.Frequency = form.find("#frequency").children(":selected").val();


    // Get the time and date passed in the form
    // First get the date, then get the string representation of time (hh:mm)
    // Finally handle timezone offset and convert the time to seconds
    let next = new Date(form.find("#date").val());
    task.Next = Math.round(next / 1000);
    let timevals = form.find("#time").val().split(":");
    task.Next += (parseInt(timevals[0]) * 60 + parseInt(timevals[1]) + next.getTimezoneOffset()) * 60;

    // Call task creation, upon success add new task form and the newly created task
    $.post("api/taskCreate.php", { Task : JSON.stringify(task), Assigned : JSON.stringify(assigned), Session : localStorage.getItem("Session") },
        function (data){
            if(data === "2002"){
                Library.LogOut();
                return;
            }

            assigned = JSON.parse(data).Assigned;
            task.Assigned = new Set([assigned]);

            form.remove();
            createTaskElement(groupEntity, task);
            createTaskForm(groupEntity);
        });
}

/**
 * Subscribes an element to "userAdd" and "userRemove" events.
 * @param element A <select> element.
 */
export function assignUserEvent(element) {
    element.addEventListener("userAdd", function(event) {
        $(element).append($("<option>", { value : event.detail.userID, text : users[event.detail.userID].Name}))
    });
    element.addEventListener("userRemove", function(event) {
        $(element).children('option[value="' + event.detail.userID.toString() + '"]').remove();
    });
}

let taskSnippet =`
<div class="box-element task">
    <div id="header" class="task-header">
        <h3 id="title" class="task-header-element"></h3>
        <button id='task-delete' class='task-header-element button float-right dgrey'>Delete</button>
    </div>
    <div class="rowbox">
        <div id="descriptionbox" class="desc-box">
            <p id="description" class="description"></p>
        </div>
        <div class="margin-left">
            <p id="deadlinetext" class="margin-6">Due by: <span id="nextdeadline"></span></p> 
            <div> <p class="margin-6"> <span id="frequency"></span> x <span id="freqmult"></span></p> </div>
        </div>
    </div>
    <div class="inline-block">
        <div id="assigned" class="inline-element"></div>
        <select id="assignedselect">
                <option value="-2" selected>Auto-assign</option>
        </select>
        <button id='task-assign' class='inline-element bottom dgrey'>Change</button>
        
        <button id='task-finish' class='inline-element float-right bottom dgrey'>Finish</button>
    </div>
</div>`;

let taskFinishedSnippet =`
<div class="box-element task">
    <div id="header" class="task-header">
        <h3 id="title" class="task-header-element"></h3>
        <button id='task-delete' class='task-header-element button float-right dgrey'>Delete</button>
    </div>
    <div class="rowbox">
        <div id="descriptionbox" class="desc-box">
            <p id="description" class="description"></p>
        </div>
        <div class="margin-left">
            <p class="margin-6">Refreshes at: <span id="nextdeadline"></span></p> 
            <div> <p class="margin-6"> <span id="frequency"></span> x <span id="freqmult"></span></p> </div>
        </div>
    </div>
    <div class="inline-block">
        <div id="assigned" class="inline-element"></div>
        <select id="assignedselect">
                <option value="-2" selected>Auto-assign</option>
        </select>
        <button id='task-assign' class='inline-element bottom dgrey'>Change</button>
    </div>
</div>`;

let formSnippet = `
<form method="post" class="box-element task">
    <div id="header" class="rowbox">
        <input id="title" class="margin-l6" placeholder="Title*">
        <div class="margin-left">
            <input id="date" type="date">
            <input id="time" type="time" value="12:00">
        </div>
    </div>
    <div class="rowbox">
        <textarea id="description" class="desc-box" placeholder="Description"></textarea>
        <div class="margin-left rowbox">
            <label for="frequency" class="margin-r6">Frequency:</label>
            <input type="number" id="freqmult" style="width: 45px" min="1" max="12" value="1" step="1">
            <select id="frequency">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </div>
    </div>
    <div class="rowbox">
        <div class="margin-l6">
            <label for="assignedselect" class="margin-r6">Assign to:</label>
            <select id="assignedselect">
                <option value="-2" selected>Auto-assign</option>
                <option value="-1">No one</option>
            </select>
        </div>
        <div class="margin-l6">
            <label for="length" class="margin-r6 margin-l6">Length of task in minutes:</label>
            <input type="number" id="length" style="width: 90px" min="1" value="30" step="1">
        </div>
        <input type="submit" id="submit" class="margin-left" value="Create">
    </div>
</form>`;