import * as G from "./Group.js";
import Task, {createTaskElement, createTaskForm} from "./Task.js";
import {users, goToEmpty, loadGroups} from "../home.js";
import * as Library from "./Library.js";

/**
 * The class representing a group's page. Stores the group's groupEntity object.
 */
export default class GroupPage
{
    groupEntity;

    constructor(GroupEntity) {
        this.groupEntity = GroupEntity;
    }

    /**
     * To be called after the html has been retrieved through AJAX.
     */
    onLoad() {
        let headDiv = $("#content");
        let groupEntity = this.groupEntity;
        let newTaskAssign = headDiv.find("#assignedselect");

        createHeaderElement(this.groupEntity.Group);

        // Remove element for user adding and removing, if the user is not the groups owner
        if(JSON.parse(localStorage.getItem("Session")).OwnerID !== this.groupEntity.Group.OwnerID) {
            headDiv.find("#usermanagement").remove();
        } else {
            // Add events for user management buttons
            headDiv.find("#member-invite").on("click", function (event) {
                event.preventDefault();
                groupMemberChange($(this).siblings("#member-name")[0].value, groupEntity, "Add");
            });
            headDiv.find("#member-remove").on("click", function (event) {
                event.preventDefault();
                groupMemberChange($(this).siblings("#member-name")[0].value, groupEntity, "Remove");
            });
        }

        // Get members and invited users of the group, store and display them, plus add them to dropdown menus
        let membersDeferred = G.getMembers(this.groupEntity).then(
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

        // Wait for users and chores and display them
        $.when(membersDeferred, G.getChores(this.groupEntity)).then(
            function (_, res) {
                for (const key in res) { // Create element for each task
                    if(res.hasOwnProperty(key)){
                          createTaskElement(groupEntity, Object.assign(new Task, res[key]));
                    }
                }

                // Create the new task form
                createTaskForm(groupEntity);
            }, function () {
                alert("Couldn't load task list!");
        });
    }
}

/**
 * Displays the group name and create delete or leave leave button, depending on ownership of group.
 * @param group The group for which to create the header.
 */
export function createHeaderElement(group)
{
    const header = $("#content").find("#group-header");

    // Show the name of the group
    header.find("#group-title").text(group.Name);

    let session = localStorage.getItem("Session");
    let parsed = JSON.parse(session);

    let button;

    // If the user is the owner, then create the group delete button
    if(parsed.OwnerID === group.OwnerID) {
        button = $($.parseHTML("<button id='group-remove' class='inline-element float-right dgrey'>Delete</button>"));

        button.on("click", function () {
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
    } else { // Otherwise create the leave button
        button = $($.parseHTML("<button id='group-leave' class='inline-element float-right dgrey'>Leave</button>"));

        button.on("click", function () {
            if ($(this).hasClass("red")) {
                $.post("api/groupLeave.php", { GroupID : group.ID, Session : session }, function (){
                    loadGroups();
                    goToEmpty();
                });
            } else {
                $(this).removeClass("dgrey");
                $(this).addClass("red");
                $(this).text("Are you sure?");
            }
        });
    }

    // Add button to header
    // Add button to header
    header.append(button);
}

/**
 * Creates a box with the users name, for the user list.
 * @param {User} user The user for which the element should be made.
 * @param {boolean} isMember Determines whether to set the border and background color.
 */
function createUserElement(user, isMember)
{
    // Create element
    let p = $("<p class='box-element padding'></p>").text(user.Name);

    // Recolor element if the user is a member
    if(isMember === true){
        p.addClass("lgrey user");
    }

    // Add element to members list
    $("#content").find("#members").append(p);
}

/**
 * Removes a users element with given username.
 * @param user The user for which the element should be deleted.
 */
function deleteUserElement(user)
{
    $("#content").find("#members").children().remove("p:contains(" + user.Name + ")");
}

/**
 * Wrapper function for 'groupMembership.php' calls, checks whether user is owner of group.
 * @param {string} Username
 * @param {GroupEntity} groupEntity
 * @param {string} action
 */
function groupMemberChange(Username, groupEntity, action)
{
    // Make sure we are not changing the group owner's state of membership
    if(users[groupEntity.Group.OwnerID].Name === Username) {
        return;
    }

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
                users[user.ID] = user; // Make sure user us stored

                if (action === "Add") {
                    groupEntity.Invited.push(user.ID);
                    createUserElement(user, false);

                    // Call event to add user to dropdown menus
                    let addEvent = new CustomEvent("userAdd", { detail: { userID: user.ID}} );
                    dispatchUserEvent(addEvent);
                } else if (action === "Remove") {
                    deleteUserElement(user);
                    // Remove users element from tasks, they are assigned to
                    $("#assigned").children().remove("#" + user.ID);

                    // Call event to remove user from dropdown menus
                    let removeEvent = new CustomEvent("userRemove", { detail: { userID: user.ID }} );
                    dispatchUserEvent(removeEvent);
                }
            }
        });
}

/**
 * Event dispatcher for dropdown boxes
 * @param {CustomEvent} userEvent
 */
function dispatchUserEvent(userEvent) {
    $("#assignedselect").each( function () {
        this.dispatchEvent(userEvent);
    })
}