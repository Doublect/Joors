import Group, * as G from "./classes/Group.js";
import User from "./classes/User.js";
import Page from "./classes/Page.js";

let groups = {};
let users = {};
let currentpage = new Page(undefined, 'homepage.html');


$(function () {
    // Load groups
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
                break;
        }

        loadGroupPage(1);
    });
});


function createGroupElement(group) {
    const sidebar = $("#sidebar");

    let p = $("<p></p>").text(group.Name);
    p.addClass("sbar-element");
    p.attr("id", group.ID);

    sidebar.append(p);
}

function createUserElement(user, pageID) {
    const membersdiv = $("#content-" + pageID).children("#members");

    let p = $("<p></p>").text(user.Name);

    membersdiv.append(p);
}

function createTaskElement (task, pageID) {

}

function loadGroupPage(groupID) {
    currentpage = new Page(currentpage, 'grouppage.html');

    G.getMembers(groups[groupID]).then(
        function (res) {
            for(let i = 0; i < Object.getOwnPropertyNames(res).length; i++) {
                let user = Object.assign(new User, res[i]);

                if(!user[user.ID])
                    users[user.ID] = user;

                createUserElement(users[user.ID], currentpage.ID);
            }
        }, function () {
            alert("Couldn't load members list!");
        });

    /*
    G.getChores(groups[groupID]).then(
        function (res) {
            for(let i = 0; i < Object.getOwnPropertyNames(res).length; i++) {
                let task = Object.assign(new User, res[i]);

                if(!user[user.ID])
                    users[user.ID] = user;

                createUserElement(users[user.ID], currentpage.ID);
            }
        }, function () {
            alert("Couldn't load members list!");
        });*/
}