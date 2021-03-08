import User from "./User.js";

export default class Group {
    constructor(ID, Name) {
        this.ID = ID;
        this.Name = Name;
    }
}

export class GroupEntity {
    //Chores;
    Members = {};
    Group;

    constructor(Group) {
        this.Group = Group;
    }
}

// ------------------------------------------------------------------------
// REQUEST

function requestChores(GroupEntity) {
    return $.post("api/groupTasksGet.php", {GroupID: GroupEntity.Group.ID, Session: localStorage.getItem('Session')});
}

function requestMembers(GroupEntity) {
    return $.post("api/groupMembersGet.php", {GroupID: GroupEntity.Group.ID, Session: localStorage.getItem('Session')});
}

// ------------------------------------------------------------------------
// GET

export async function getChores(groupEntity) {
    if(!groupEntity.Chores) {
        let data = await requestChores(groupEntity);

        groupEntity.Chores = JSON.parse(data);
    }
    return groupEntity.Chores;
}

export async function getMembers(groupEntity) {
    if(Object.getOwnPropertyNames(groupEntity.Members).length === 0) {
        await requestMembers(groupEntity).then( function (data) {
            let parsed = JSON.parse(data);

            for (let i = 0; i < parsed.length; i++) {
                groupEntity.Members[i] = Object.assign(new User, parsed[i]);
            }

            console.log(groupEntity.Members)
        });
    }
    return groupEntity.Members;
}