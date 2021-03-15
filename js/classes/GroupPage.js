import * as G from "./Group.js";
import Task, {createTaskElement, createTaskForm, assignUserEvent, parseNewTaskForm} from "./Task.js";
import Page from "./Page.js";
import {users, goHome, loadGroups} from "../home.js";
import * as Library from "../Library.js";

export default class GroupPage extends Page
{
    groupEntity;

    constructor(previous, current, GroupEntity) {
        super(previous, current);
        this.groupEntity = GroupEntity;
    }

    onLoad() {
        let pageID = this.ID;
        let groupEntity = this.groupEntity;
        let membersreq;
        let newtaskassign = $("#assignedselect");

        createHeaderElement(this.groupEntity.Group, pageID);

        if(JSON.parse(localStorage.getItem("Session")).OwnerID !== this.groupEntity.Group.OwnerID) {
            $("#usermanagement").remove();
        }

        if (this.groupEntity.Members.length === 0) {
             membersreq = G.getMembers(this.groupEntity).then(
                function (res) {
                    for (let i = 0; i < res.length; i++) {
                        if(!users[res[i].ID])
                            users[res[i].ID] = res[i];

                        createUserElement(res[i], pageID);
                        Library.addUserAsOption(res[i], newtaskassign);
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
                Library.addUserAsOption(this.groupEntity.Members[i], newtaskassign);
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

        // Get chores and display them
        $.when(membersreq, G.getChores(this.groupEntity)).then(
            function (memres, res) {
                for (const key in res) {
                    createTaskElement(res[key], pageID);
                }

                createTaskForm(groupEntity, pageID);
            }, function () {
                alert("Couldn't load task list!");
        });
    }
}

export function createHeaderElement(group, pageID)
{
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

function createUserElement(user, pageID)
{
    const membersdiv = $("#content-" + pageID).find("#members");

    let p = $("<p></p>").text(user.Name);
    p.addClass("box-element");

    membersdiv.append(p);
}

function deleteUserElement(user, pageID)
{
    $("#content-" + pageID).find("#members").children().remove("p:contains(" + user.Name + ")");
}

function groupMemberChange(Username, groupEntity, action, pageID)
{
    $.post("api/groupMembership.php", { Action : action, Username : Username, GroupID : groupEntity.Group.ID, Session : localStorage.getItem("Session")},
        function (data) {
            if(data === "") {
                return;
            }

            if(data === "2002") {
                Library.LogOut();
                return;
            }

            let user = JSON.parse(data);

            if(user.Name) {
                users[user.ID] = user;

                if (action === "Add") {
                    groupEntity.Members.push(user.ID);
                    createUserElement(user, pageID);

                    let addEvent = new CustomEvent("userAdd", { detail: { userID: user.ID}} );
                    dispatchUserEvent(addEvent);

                } else if (action === "Remove") {
                    deleteUserElement(user, pageID);

                    let removeEvent = new CustomEvent("userRemove", { detail: { userID: user.ID }} );
                    dispatchUserEvent(removeEvent);
                }
            }
        });
}

function dispatchUserEvent(userEvent) {
    $("#assignedselect").each( function () {
        this.dispatchEvent(userEvent);
    })
}