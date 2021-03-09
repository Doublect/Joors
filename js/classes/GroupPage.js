import * as G from "./Group.js";
import User from "./User.js";
import Task from "./Task.js";
import Page from "./Page.js";
import {users, goHome, loadGroups} from "../home.js";

export default class GroupPage extends Page {
    groupEntity;

    constructor(previous, current, GroupEntity) {
        super(previous, current);
        this.groupEntity = GroupEntity;
    }

    onLoad() {
        let pageID = this.ID;
        let groupEntity = this.groupEntity;
        let membersreq;
        let newtaskassign = $("#assigned");

        createHeaderElement(this.groupEntity.Group, pageID);

        if (this.groupEntity.Members.length === 0) {
             membersreq = G.getMembers(this.groupEntity).then(
                function (res) {
                    for (let i = 0; i < res.length; i++) {
                        if(!users[res[i].ID])
                            users[res[i].ID] = res[i];

                        createUserElement(res[i], pageID);
                        addUserNewTaskOption(res[i], newtaskassign);
                    }

                    let invited = groupEntity.Invited;
                    for (let i = 0; i < invited.length; i++) {

                        if(!users[invited[i].ID])
                            users[invited[i].ID] = invited[i];

                        createUserElement(invited[i], pageID);
                    }
                }, function () {
                    alert("Couldn't load members list!");
                });
        } else {
            for (let i = 0; i < this.groupEntity.Members.length; i++) {
                createUserElement(this.groupEntity.Members[i], this.ID);
                addUserNewTaskOption(this.groupEntity.Members[i], newtaskassign);
            }
        }

        $("#addbtn").on("click", function (event) {
            event.preventDefault();
            groupMemberChange($("#username")[0].value, groupEntity, "Add", pageID);
        });
        $("#removebtn").on("click", function (event) {
            event.preventDefault();
            groupMemberChange($("#username")[0].value, groupEntity, "Remove", pageID);
        });

        if (this.groupEntity.Chores.length === 0) {
            $.when(membersreq, G.getChores(this.groupEntity)).then(
                function (memres, res) {
                    for (let i = 0; i < res.length; i++) {
                        let task = Object.assign(new Task, res[i]);

                        createTaskElement(task, pageID);
                    }
                }, function () {
                    alert("Couldn't load task list!");
                });
        } else {
            for (let i = 0; i < this.groupEntity.Chores.length; i++) {
                createTaskElement(this.groupEntity.Chores[i], this.ID);
            }
        }
    }
}

let taskSnippet =`
<div class="box-element task">
    <div id="header" class="task-header">
        <h3 id="title" class="task-header-element"></h3>
        <p id="delete" class="task-header-element float-right">Delete</p>
        <p class="task-header-element float-right">Options</p>
    </div>
    <div>
        <p id="description" class="inline-element description">Description</p>
        <p class="inline-element float-right">Due by:</p>

    </div>
    <div>
        <div id="assigned" class="inline-element"></div>
        <form class="inline-element float-right">
            <input class="inline-element" type="file">
            <input class="inline-element" type="submit">
        </form>
    </div>
</div>`;

export function createHeaderElement(group, pageID) {
    const header = $("#content-" + pageID).find("#group-header");

    // Show the name of the groupEntity
    header.find("#group-title").text(group.Name);

    let session = localStorage.getItem("Session");
    let parsed = JSON.parse(session);

    if(parsed.OwnerID === group.OwnerID) {
        let deletebtn = $($.parseHTML("<button id='group-remove' class='inline-element float-right dgrey'>Delete</button>"));

        deletebtn.on("click", function () {
            if ($(this).hasClass("red")) {
                $.post("api/groupDelete.php", { GroupID : group.ID, Session : session }, function (){
                    loadGroups();
                    goHome();
                });
            } else {
                $(this).removeClass("dgrey");
                $(this).addClass("red");
                $(this).text("Are you sure?");
            }
        });

        header.append(deletebtn);
    }
}

function createUserElement(user, pageID) {
    const membersdiv = $("#content-" + pageID).find("#members");

    let p = $("<p></p>").text(user.Name);
    p.addClass("box-element");

    membersdiv.append(p);
}

function deleteUserElement(user, pageID) {
    $("#content-" + pageID).find("#members").children().remove("p:contains(" + user.Name + ")");
}

function addUserNewTaskOption(user, elem) {
    let option = $("<option></option>");

    option.attr("id", user.ID);
    option.text(user.Name);

    elem.append(option);
}

export function createTaskElement(task, pageID) {
    const tasksdiv = $("#content-" + pageID).find("#tasks");

    let taskElem = $($.parseHTML(taskSnippet));

    taskElem.find("#title").text(task.Name);
    taskElem.find("#description").text(task.Desc);
    //taskelem.find($("#delete")).on("click")

    let assignelem = taskElem.find("#assigned");

    for(let i = 0; i < task.Assigned.length; i++) {
        let p = $("<p></p>").text(users[task.Assigned[i]].Name);
        p.addClass("inline-element");

        assignelem.append(p);
    }

    tasksdiv.append(taskElem);
}

function groupMemberChange(Username, groupEntity, action, pageID) {
    $.post("api/groupMembership.php", { Action : action, Username : Username, GroupID : groupEntity.Group.ID, Session : localStorage.getItem("Session")},
        function (data) {
            if(data === "") {
                return;
            }

            if(data === "2002") {
                localStorage.clear();
                window.location.href = "index.html";
                return;
            }

            let user = JSON.parse(data);

            if(user.Name) {
                users[user.ID] = user;

                if (action === "Add") {
                    groupEntity.Members.push(user.ID);
                    createUserElement(user, pageID);
                } else if (action === "Remove") {
                    deleteUserElement(user, pageID);
                }
            }
        });
}

function parseNewTaskForm(form) {
    let task = new Task;

    // Make sure the title is set
    task.Name = form.find("#title").val();
    if(task.Name === "") {
        return;
    }

    // Read in fields
    task.Desc = form.find("#desc");
    task.Length = form.find("#length");

    // Get the selected elements from the drop-downs
    task.Assigned = form.find("#assigned").children(":selected").attr("id");
    task.Frequency = form.find("#frequency").children(":selected").attr("id");

    // Get the time and date passed in the form
    // First get the date, then get the string representation of time (hh:mm)
    // Finally convert the time to seconds
    task.Next = Math.round(new Date(form.find("#date").val()).getTime() / 1000);
    let timevals = form.find("#time").val().split(":");
    task.Next += (parseInt(timevals[0]) * 60 + parseInt(timevals[1])) * 60;

    $.post()
}