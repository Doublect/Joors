import Group, * as G from "./classes/Group.js";
import Page from "./classes/Page.js";
import GroupPage from "./classes/GroupPage.js";

let groups = {};
export let users = {};
let currentpage;

$(function () {
    // Load groups
    $.post('api/groupsGet.php', { Session : localStorage.getItem("Session") }, function (data) {
        switch(data) {
            case "2002":
                //TODO: logout if no session active
                break;
            default:
                data = JSON.parse(data);

                for(let i = data.length; i >= 0; i--) {
                    let group = Object.assign(new Group, data[i]);
                    groups[group.ID] = new G.GroupEntity(group);

                    createGroupElement(group);
                }

                currentpage = new Page(undefined, 'homepage.html');
                break;
        }
    });

    addControlBindings();
});

function addControlBindings() {
    $("#previousbtn").on("click", function () {
        currentpage = currentpage.previous();
    });

    $("#homebtn").on("click", function () {
        while(currentpage.ID > 0) {
            currentpage = currentpage.remove();
        }
        currentpage.show();
    });
}

function createGroupElement(group) {
    const sidebar = $("#sidebar");

    let p = $("<p></p>").text(group.Name);
    p.addClass("sbar-element");
    p.attr("id", group.ID);

    p.on("click", function () {
        loadGroupPage(group.ID);
    });

    sidebar.prepend(p);
}

function loadGroupPage(groupID) {
    currentpage = new GroupPage(currentpage, 'grouppage.html', groups[groupID]);
}