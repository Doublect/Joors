import {users} from "../home.js";
import * as Library from "../Library.js";

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

export function createTaskElement(task, pageID)
{
    const tasksdiv = $("#content-" + pageID).find("#tasks");

    let taskElem = $($.parseHTML(taskSnippet));

    taskElem.find("#title").text(task.Name);
    taskElem.find("#description").text(task.Desc);

    taskElem.find("#frequency").text(task.Frequency);
    taskElem.find("#freqmult").text(task.FreqMult);

    let dateobj = new Date(task.Next * 1000);

    const year = dateobj.getFullYear().toString().slice(-2);
    const month = dateobj.getMonth();
    const day = dateobj.getDate();
    const hour = dateobj.getHours();
    let minutes = dateobj.getMinutes();
    if(minutes < 10) minutes = "0" + minutes.toString();
    taskElem.find("#nextdeadline").text(year + "/" + month + "/" + day + " " + hour + ":" + minutes);


    if(task.Assigned) {
        let assignelem = taskElem.find("#assigned");

        for(let id of task.Assigned){
            let p = $("<p></p>").text(users[id].Name);
            p.addClass("inline-element");

            assignelem.append(p);
        }
    }

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

    tasksdiv.append(taskElem);
}

export function createTaskForm(groupEntity, pageID)
{
    let formElem = $($.parseHTML(formSnippet));

    let date = formElem.find("#date");
    date[0].valueAsDate = new Date();
    date[0].min = new Date().toISOString().split("T")[0];

    let assigned = formElem.find("#assignedselect");

    groupEntity.Members.forEach(function (user) {
        Library.addUserAsOption(user, assigned);
    });

    assignUserEvent(assigned[0]);

    formElem.find("#submit").on("click", function (event){
        event.preventDefault();
        parseNewTaskForm(formElem, groupEntity, pageID);
    });

    $("#content-" + pageID).find("#taskcreate").append(formElem);
}

export function parseNewTaskForm(form, groupEntity, pageID)
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
    task.Completed = 0;

    // Get the selected elements from the drop-downs
    let assigned = form.find("#assignedselect").children(":selected").val();
    task.Frequency = form.find("#frequency").children(":selected").val();

    // Get the time and date passed in the form
    // First get the date, then get the string representation of time (hh:mm)
    // Finally convert the time to seconds
    task.Next = Math.round(new Date(form.find("#date").val()).getTime() / 1000);
    let timevals = form.find("#time").val().split(":");
    task.Next += (parseInt(timevals[0]) * 60 + parseInt(timevals[1])) * 60;

    $.post("api/taskCreate.php", { Task : JSON.stringify(task), Assigned : JSON.stringify(assigned), Session : localStorage.getItem("Session") },
        function (data){
            if(data === "2002"){
                Library.LogOut();
                return;
            }

            assigned = JSON.parse(data).Assigned;
            if(assigned > 0) {
                task.Assigned = new Set([assigned]);
            }

            form.remove();
            createTaskElement(task, pageID);
            createTaskForm(groupEntity, pageID);
        });
}

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
            <p class="margin-6">Due by: <span id="nextdeadline"></span></p> 
            <div> <p class="margin-6"> <span id="frequency"></span> x <span id="freqmult"></span></p> </div>
        </div>
    </div>
    <div class="inline-block">
        <div id="assigned" class="inline-element"></div>
        <button id='task-finish' class='inline-element float-right bottom dgrey'>Finish</button>
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
            <input type="number" id="freqmult" min="1" max="12" value="1" step="1">
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
            <label for="length" class="margin-r6">Length of task:</label>
            <input type="number" id="length"  min="1" value="30" step="1">
        </div>
        <input type="submit" id="submit" class="margin-left" value="Create">
    </div>
</form>`;