import * as G from "./Group.js";
import User from "./User.js";
import Task from "./Task.js";
import Page from "./Page.js";
import {users, goHome, loadGroups} from "../home.js";

export default class GroupPage extends Page {
    constructor(previous, current, GroupEntity) {
        super(previous, current);
        this.group = GroupEntity;
    }

    onLoad() {
        let pageID = this.ID;
        let membersreq;


        createHeaderElement(this.group.Group, pageID);
        bindCollapsible();

        if (this.group.Members.length === 0) {
             membersreq = G.getMembers(this.group).then(
                function (res) {
                    if(res === "2002"){
                        //TODO: logout if no session active
                    }

                    for (let i = 0; i < res.length; i++) {
                        let user = Object.assign(new User, res[i]);

                        if(!users[user.ID])
                            users[user.ID] = user;

                        createUserElement(user, pageID);
                    }
                }, function () {
                    alert("Couldn't load members list!");
                });
        } else {
            for (let i = 0; i < this.group.Members.length; i++) {
                createUserElement(this.group.Members[i], this.ID);
            }
        }

        if (this.group.Chores.length === 0) {
            $.when(membersreq, G.getChores(this.group)).then(
                function (memres, res) {
                    if(res === "2002"){
                        //TODO: logout if no session active
                    }

                    for (let i = 0; i < res.length; i++) {
                        let task = Object.assign(new Task, res[i]);

                        createTaskElement(task, pageID);
                    }
                }, function () {
                    alert("Couldn't load task list!");
                });
        } else {
            for (let i = 0; i < this.group.Chores.length; i++) {
                createTaskElement(this.group.Chores[i], this.ID);
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
    const header = $("#content-" + pageID).children("#group-header");

    // Show the name of the group
    header.children("#group-title").text(group.Name);

    let session = localStorage.getItem("Session")
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

export function createUserElement(user, pageID) {
    const membersdiv = $("#content-" + pageID).find("#members");

    let p = $("<p></p>").text(user.Name);
    p.addClass("box-element");

    membersdiv.append(p);
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

function bindCollapsible() {
    let collapsibles = $(".collapsible");

    collapsibles.each( function () {
       $(this).on("click", function () {
           $(this).toggleClass("active");
           let content = $(this).next();
           if(content.is(":visible")){
               content.hide();
           } else {
               content.show();
           }
       })
    });
}