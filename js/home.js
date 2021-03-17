import Group, * as G from "./classes/Group.js";
import GroupPage from "./classes/GroupPage.js";
import * as Library from "./classes/Library.js";

let groups = {};
export let users = {};
let currentPage;

$(function () {
    // Load groups
    loadGroups();
});

/**
 * Loads the sidebar's content, refreshing if necessary.
 */
export function loadGroups() {
    // Empty current content from sidebar, so a user can't do anything while the new state is loaded in
    $("#sidebar").empty();

    // API call to retrieve the data of groups
    $.post('api/groupsGet.php', { Session : localStorage.getItem("Session") }, function (data) {
        switch (data){
            case "2002": // No session
                Library.LogOut();
                break;
            case "": // No data
                break;
            default:
                data = JSON.parse(data);

                // Make sure data.Member exists, these are the groups the user is a member of
                if(data.Member) {
                    for (let i = 0; i < data.Member.length; i++) {
                        // Cast received data into correct type (Group) and store it
                        let group = Object.assign(new Group, data.Member[i]);
                        groups[group.ID] = new G.GroupEntity(group);

                        // Create group element on the sidebar
                        createGroupElement(group);
                    }
                }

                // Make sure data.Invited exists
                if(data.Invited) {
                    for (let i = 0; i < data.Invited.length; i++) {
                        // Cast received data into correct type (Group) and store it
                        let group = Object.assign(new Group, data.Invited[i]);
                        groups[group.ID] = new G.GroupEntity(group);

                        // Create invitation element on the sidebar
                        createInvititationElement(group);
                    }
                }

                // Add the group creation button to sidebar
                groupCreate();

                // If no page is loaded then load the first group or an empty page
                if(currentPage === undefined) {
                    if(data.Member) {
                        loadGroupPage(data.Member[0].ID)
                    } else {
                        goToEmpty();
                    }
                }
                break;
        }
    });
}

/**
 * Handles group creation logic:
 * First, creates a button called "Create new group"
 * On click, the button is replaced by a form
 * When submitted with a name, calls 'groupCreate.php' and reloads sidebar
 */
function groupCreate() {
    const sidebar = $("#sidebar");
    let createButton = $("#groupbtn");
    let p;

    // Check if createButton exists
    if(createButton.length === 0) {
        // Make sure there is no form
        $("#groupform").remove();

        // Create button
        p = $("<p id='groupbtn' class='sbar-element'>Create new group</p>");

        // Make sure button calls this function
        p.on("click", function (){
            groupCreate();
        });

        // Add an extra line, to separate from invitations/groups
        sidebar.append($("<hr>"));
    } else {
        // Make sure there is no button
        createButton.remove();

        // Create form
        p = $("<form id='groupform' class='rowbox'> <input id='groupname'> <input class='dgrey' type='submit' value='Create'></form>");


        p.on("submit", function (){
            let name = $("#groupform").find("#groupname").val();

            // Check name length
            if(name.length > 0 && name.length <= 64) {
                $.post('api/groupCreate.php', {Name: name, Session: localStorage.getItem("Session")}, function () {
                    loadGroups();
                });
            }
        });
    }

    // Add to sidebar
    sidebar.append(p);
}

/**
 * Adds a button for the group, which on click opens the group's page.
 * @param {Group} group The group for which the sidebar entry should be made. Requires Name and ID.
 */
function createGroupElement(group) {
    const sidebar = $("#sidebar");

    // Create element
    let p = $("<p class='sbar-element'></p>").text(group.Name);
    p.attr("id", group.ID);

    // On click load group's page
    p.on("click", function () {
        loadGroupPage(group.ID);
    });

    // Add to sidebar
    sidebar.append(p);
}

/**
 * Adds a invitation, which can be accepted or rejected, using the 'inviteSnippet' html string.
 * @param group The group for which the sidebar entry should be made. Requires Name and ID.
 */
function createInvititationElement(group) {
    // Create element
    let inv = $($.parseHTML(inviteSnippet));
    inv.find("#groupname").text(group.Name);

    // Logic for accept button
    inv.find("#invite-acc").on("click", function () {
        handleInvitation(group.ID, "Add");
    });

    // Logic for reject button
    inv.find("#invite-dec").on("click", function () {
        handleInvitation(group.ID, "Remove");
    });

    // Add to sidebar
    $("#sidebar").append(inv);
}

/**
 * Wrapper function for 'groupInvitation.php' calls, updates sidebar on success.
 * @param {number} groupID The group for which the user accepted or rejected the inviation.
 * @param {string} action The type of the action either "Add" or "Remove".
 */
function handleInvitation (groupID, action) {
    $.post("api/groupInvitation.php", { Action : action, GroupID : groupID, Session : localStorage.getItem("Session")},
        function (data){
            if(data === "2002") {
                return Library.LogOut();
            }
            loadGroups();
        })
}

/**
 * Loads the 'group.html' page of specified group.
 * @param groupID The group which is to be shown.
 */
export function loadGroupPage(groupID) {
    if(currentPage) {
        // Remove current page's elements
        $("#content").remove();
    }

    currentPage = new GroupPage(groups[groupID]);
    let div = $("<div id='content'></div>");

    div.load('group.html', function () {
        currentPage.onLoad();
    });

    $("#contentbox").append(div);
}

/**
 * Loads an empty page.
 */
export function goToEmpty() {
    if(currentPage) {
        // Remove current page's elements
        $("#content").remove();

        currentPage = undefined;
    }

    // Load empty page
    let div = $("<div id='content'></div>");
    $("#contentbox").append(div);
    div.load('empty.html');
}

let inviteSnippet = `
    <hr>
    <div class="sbar-element rowbox">
        <p id="groupname"></p>
        <div class="margin-left">
            <button id='invite-acc' class='green'>Accept</button>
            <button id='invite-dec' class='margin-l6 red'>Decline</button>
        </div>
    </div>
`;