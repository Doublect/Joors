import User from "./User.js";
import Task from "./Task.js";
import * as Library from "./Library.js";

/**
 * The class representing the php version of a group.
 */
export default class Group {
    ID;
    Name;
    OwnerID;

    constructor(ID, Name, OwnerID) {
        this.ID = ID;
        this.Name = Name;
        this.OwnerID = OwnerID;
    }
}

/**
 * An extension of Group, which connects tasks and users to a group.
 */
export class GroupEntity {
    Chores = {};
    Members = [];
    Invited = [];
    Group;

    constructor(group) {
        this.Group = group;
    }
}

// ------------------------------------------------------------------------
// REQUEST

function requestChores(groupEntity) {
    return $.post("api/groupTasksGet.php", {GroupID: groupEntity.Group.ID, Session: localStorage.getItem('Session')});
}

function requestMembers(groupEntity) {
    return $.post("api/groupMembersGet.php", {GroupID: groupEntity.Group.ID, Session: localStorage.getItem('Session')});
}

// ------------------------------------------------------------------------
// GET

export async function getChores(groupEntity) {
    await requestChores(groupEntity).then( function (data) {
        if(data === "2002"){
            Library.LogOut();
            return;
        }

        let parsed = JSON.parse(data);

        // Cast and store tasks
        for (let i = 0; i < parsed.length; i++) {
            groupEntity.Chores[parsed[i].ID] = Object.assign(new Task, parsed[i]);
            groupEntity.Chores[parsed[i].ID].Assigned = new Set(groupEntity.Chores[parsed[i].ID].Assigned);
        }
    });

    return groupEntity.Chores;
}

export async function getMembers(groupEntity) {
    await requestMembers(groupEntity).then( function (data) {
        if(data === "2002"){
            Library.LogOut();
            return;
        }

        let parsed = JSON.parse(data);

        for (let i = 0; i < parsed.Members.length; i++) {
            groupEntity.Members.push(Object.assign(new User, parsed.Members[i]));
        }

        if(parsed.Invited) {
            for (let i = 0; i < parsed.Invited.length; i++) {
                groupEntity.Invited.push(Object.assign(new User, parsed.Invited[i]));
            }
        }
    });

    return groupEntity.Members;
}