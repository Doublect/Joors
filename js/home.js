import Group, * as G from "./classes/Group.js";
import Page, {loadPage} from "./classes/Page.js";
import GroupPage from "./classes/GroupPage.js";
import * as Library from "./Library.js";

let groups = {};
export let users = {};
let currentPage, homePage;

$(function () {
    // Load groups
    loadGroups();

    addControlBindings();
});

function addControlBindings() {
    $("#previousbtn").on("click", function () {
        currentPage = currentPage.previous();
    });

    $("#homebtn").on("click", function () {
        goHome();
    });
}

export function loadGroups() {
    $("#sidebar").empty();

    $.post('api/groupsGet.php', { Session : localStorage.getItem("Session") }, function (data) {
        if(data === "2002") {
            Library.LogOut();
        } else {
            if(data !== "") {
                data = JSON.parse(data);

                for (let i = 0; i < data.Member.length; i++) {
                    let group = Object.assign(new Group, data.Member[i]);
                    groups[group.ID] = new G.GroupEntity(group);

                    createGroupElement(group);
                }

                if(data.Invited) {
                    for (let i = 0; i < data.Invited.length; i++) {
                        let group = Object.assign(new Group, data.Invited[i]);
                        groups[group.ID] = new G.GroupEntity(group);

                        createInvititationElement(group);
                    }
                }
            }

            groupCreate();

            if(currentPage === undefined) {
                //currentPage = new Page(undefined, 'homepage.html');
                if(data.Member[0]) {
                    currentPage = new GroupPage(homePage, 'grouppage.html', groups[data.Member[0].ID]);
                } else {
                    currentPage = new Page(undefined, 'homepage.html');
                }

                loadPage(currentPage);
                homePage = currentPage;
            }
        }
    });
}

function groupCreate() {
    const sidebar = $("#sidebar");

    let createbutton = $("#groupbtn");
    let createform = $("#groupform");

    if(createbutton.length === 0) {
        createform.remove();

        let p = $($.parseHTML("<p id='groupbtn' class='sbar-element'>Create new group</p>"));

        p.on("click", function (){
            groupCreate();
        });

        sidebar.append(p);
    } else {
        createbutton.remove();

        let p = $($.parseHTML("<form id='groupform' style='padding-left: 20px;'> <input id='groupname'> <input class='dgrey' type='submit' value='Create'></form>"));

        p.on("submit", function (){
            let name = $("#groupform").find("#groupname").val();

            if(name.length > 0) {
                $.post('api/groupCreate.php', {Name: name, Session: localStorage.getItem("Session")}, function () {
                    loadGroups();
                });
            }

            groupCreate();
        });

        sidebar.append(p);
    }

}

function createGroupElement(group) {
    const sidebar = $("#sidebar");

    let p = $("<p></p>").text(group.Name);
    p.addClass("sbar-element");
    p.attr("id", group.ID);

    p.on("click", function () {
        loadGroupPage(group.ID);
    });

    sidebar.append(p);
}

function createInvititationElement(group) {
    const sidebar = $("#sidebar");

    let p = $("<p></p>").text(group.Name);
    p.addClass("sbar-element");
    p.attr("id", group.ID);

    let acc = $("<p></p>").text("Accept");
    let dec = $("<p></p>").text("Decline");

    acc.addClass("sbar-element inline-element");
    acc.attr("id", group.ID + "btn");
    dec.addClass("sbar-element inline-element");
    dec.attr("id", group.ID + "btn");

    acc.on("click", function () {
        handleInvitation(group.ID, "Add");
    });

    dec.on("click", function () {
        handleInvitation(group.ID, "Remove");
    });

    sidebar.append(p);
    sidebar.append(acc);
    sidebar.append(dec);
}

function handleInvitation (groupID, action) {
    $.post("api/groupInvitation.php", { Action : action, GroupID : groupID, Session : localStorage.getItem("Session")},
        function (){
            loadGroups();
        })
}

function loadGroupPage(groupID) {
    currentPage.previous();
    currentPage = new GroupPage(homePage, 'grouppage.html', groups[groupID]);

    loadPage(currentPage);
}

export function goHome() {
    while(currentPage.ID > 0) {
        currentPage = currentPage.remove();
    }
    currentPage.show();
}