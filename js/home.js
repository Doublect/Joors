import Group, * as G from "./classes/Group.js";
import Page from "./classes/Page.js";
import GroupPage from "./classes/GroupPage.js";

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
        switch(data) {
            case "2002":
                //TODO: logout if no session active
                break;
            default:
                data = JSON.parse(data);

                for(let i = 0; i < data.length; i++) {
                    let group = Object.assign(new Group, data[i]);
                    groups[group.ID] = new G.GroupEntity(group);

                    createGroupElement(group);
                }

                groupCreate();

                if(currentPage === undefined) {
                    currentPage = new Page(undefined, 'homepage.html');
                    homePage = currentPage;
                }
                break;
        }
    });
}

function groupCreate() {
    const sidebar = $("#sidebar");

    let createbutton = $("#groupbtn");
    let createform = $("#groupform")

    if(createbutton.length === 0) {
        createform.remove();

        let p = $($.parseHTML("<p id='groupbtn' class='sbar-element'>Create new group</p>"));

        p.on("click", function (){
            groupCreate();
        });

        sidebar.append(p);
    } else {
        createbutton.remove();

        let p = $($.parseHTML("<form id='groupform' class='sbar-element'> <input id='groupname'> <input type='submit' value='Create'></form>"));

        p.on("submit", function (){
            let name = $("#groupform").find("#groupname").val();

            $.post('api/groupCreate.php', { Name : name, Session : localStorage.getItem("Session") }, function () {
                loadGroups();
            })

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

function loadGroupPage(groupID) {
    currentPage.previous();
    currentPage = new GroupPage(homePage, 'grouppage.html', groups[groupID]);
}

export function goHome() {
    while(currentPage.ID > 0) {
        currentPage = currentPage.remove();
    }
    currentPage.show();
}