import * as G from "./Group.js";
import User from "./User.js";
import Task from "./Task.js";
import Page from "./Page.js";
import {users} from "../home.js";

export default class GroupPage extends Page {
    constructor(previous, current, GroupEntity) {
        super(previous, current);
        this.group = GroupEntity;
    }

    onLoad() {
        let pageID = this.ID;

        if (this.group.Members.length === 0) {
            G.getMembers(this.group).then(
                function (res) {
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
            G.getChores(this.group).then(
                function (res) {
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

let tasksnippet =`
<div class="box-element task">
    <div id="header" class="task-header">
        <p id="title" class="task-header-element"></p>
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

export function createUserElement(user, pageID) {
    const membersdiv = $("#content-" + pageID).children("#members");

    let p = $("<p></p>").text(user.Name);
    p.addClass("box-element");

    membersdiv.append(p);
}

export function createTaskElement(task, pageID) {
    const tasksdiv = $("#content-" + pageID).children("#tasks");

    let taskelem = $($.parseHTML(tasksnippet));

    taskelem.find("#title").text(task.Name);
    taskelem.find("#description").text(task.Desc);
    //taskelem.find($("#delete")).on("click")

    let assignelem = taskelem.find("#assigned");

    for(let i = 0; i < task.Assigned.length; i++) {
        let p = $("<p></p>").text(users[task.Assigned[i]].Name);
        p.addClass("inline-element");

        assignelem.append(p);
    }

    tasksdiv.append(taskelem);
}