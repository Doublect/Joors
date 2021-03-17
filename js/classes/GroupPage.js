import * as G from "./Group.js";
import Task, {createTaskElement, createTaskForm} from "./Task.js";
import {users, goToEmpty, loadGroups} from "../home.js";
import * as Library from "./Library.js";

export default class GroupPage
{
    groupEntity;

    constructor(GroupEntity) {
        this.groupEntity = GroupEntity;
    }

    onLoad() {
        let headDiv = $("#content");
        let groupEntity = this.groupEntity;
        let membersDeferred;
        let newTaskAssign = headDiv.find("#assignedselect");

        createHeaderElement(this.groupEntity.Group);

        if(JSON.parse(localStorage.getItem("Session")).OwnerID !== this.groupEntity.Group.OwnerID) {
            headDiv.find("#usermanagement").remove();
        }

        if (this.groupEntity.Members.length === 0) {
             membersDeferred = G.getMembers(this.groupEntity).then(
                function (res) {
                    for (let i = 0; i < res.length; i++) {
                        if(!users[res[i].ID])
                            users[res[i].ID] = res[i];

                        createUserElement(res[i], true);
                        Library.addUserAsOption(res[i], newTaskAssign);
                    }

                    let invited = groupEntity.Invited;
                    for (let i = 0; i < invited.length; i++) {

                        if(!users[invited[i].ID])
                            users[invited[i].ID] = invited[i];

                        createUserElement(invited[i], false);
                    }
                }, function () {
                    alert("Couldn't load members list!");
                });
        } else {
            for (let i = 0; i < this.groupEntity.Members.length; i++) {
                createUserElement(this.groupEntity.Members[i], true);
                Library.addUserAsOption(this.groupEntity.Members[i], newTaskAssign);
            }
            for (let i = 0; i < this.groupEntity.Invited.length; i++) {
                createUserElement(this.groupEntity.Invited[i], false);
            }
        }

        headDiv.find("#member-invite").on("click", function (event) {
            event.preventDefault();
            groupMemberChange($(this).siblings("#member-name")[0].value, groupEntity, "Add");
        });
        headDiv.find("#member-remove").on("click", function (event) {
            event.preventDefault();
            groupMemberChange($(this).siblings("#member-name")[0].value, groupEntity, "Remove");
        });

        // Wait for users and chores and display them
        $.when(membersDeferred, G.getChores(this.groupEntity)).then(
            function (_, res) {
                for (const key in res) {
                    if(res.hasOwnProperty(key)){
                          createTaskElement(groupEntity, Object.assign(new Task, res[key]));
                    }
                }

                createTaskForm(groupEntity);
            }, function () {
                alert("Couldn't load task list!");
        });
    }
}

export function createHeaderElement(group)
{
    const header = $("#content").find("#group-header");

    // Show the name of the groupEntity
    header.find("#group-title").text(group.Name);

    let session = localStorage.getItem("Session");
    let parsed = JSON.parse(session);

    if(parsed.OwnerID === group.OwnerID) {
        let deleteButton = $($.parseHTML("<button id='group-remove' class='inline-element float-right dgrey'>Delete</button>"));

        deleteButton.on("click", function () {
            if ($(this).hasClass("red")) {
                $.post("api/groupDelete.php", { GroupID : group.ID, Session : session }, function (){
                    loadGroups();
                    goToEmpty();
                });
            } else {
                $(this).removeClass("dgrey");
                $(this).addClass("red");
                $(this).text("Are you sure?");
            }
        });

        header.append(deleteButton);
    }
}

function createUserElement(user, isMember)
{
    const membersdiv = $("#content").find("#members");

    let p = $("<p class='box-element padding'></p>").text(user.Name);

    if(isMember === true){
        p.addClass("lgrey user");
    }

    membersdiv.append(p);
}

function deleteUserElement(user)
{
    $("#content").find("#members").children().remove("p:contains(" + user.Name + ")");
}

function groupMemberChange(Username, groupEntity, action)
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
                    groupEntity.Invited.push(user.ID);
                    createUserElement(user, false);

                    let addEvent = new CustomEvent("userAdd", { detail: { userID: user.ID}} );
                    dispatchUserEvent(addEvent);

                } else if (action === "Remove") {
                    deleteUserElement(user);

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