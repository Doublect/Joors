import User from "./User.js";

export default class Group {
    constructor(ID, Name, OwnerID) {
        this.ID = ID;
        this.Name = Name;
        this.OwnerID = OwnerID;
    }
}

export class GroupEntity {
    Chores = [];
    Members = [];
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
    let data = await requestChores(groupEntity);
    groupEntity.Chores = JSON.parse(data);
    return groupEntity.Chores;
}

export async function getMembers(groupEntity) {
    await requestMembers(groupEntity).then( function (data) {
        let parsed = JSON.parse(data);

        for (let i = 0; i < parsed.length; i++) {
            groupEntity.Members.push(Object.assign(new User, parsed[i]));
        }
    });

    return groupEntity.Members;
}