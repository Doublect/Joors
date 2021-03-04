class Group {
    constructor(ID, Name) {
        this.ID = ID;
        this.Name = Name;
    }
}

class GroupEntity {
    chores;
    members;
    Group;

    constructor(Group) {
        this.Group = Group;
    }


    // ------------------------------------------------------------------------
    // REQUEST

    requestChores() {
        let session = localStorage.getItem('Session');
        return $.post("api/groupTasksGet.php", {GroupID: this.Group.ID, Session: JSON.stringify(session)});
    }

    requestMembers() {
        let session = localStorage.getItem('Session');
        return $.post("api/groupMembersGet.php", {GroupID: this.Group.ID, Session: JSON.stringify(session)});
    }

    // ------------------------------------------------------------------------
    // GET

    async getChores() {
        if(!this.chores) {

            let data = await this.requestChores();

            this.chores = JSON.parse(data);
        }
        return this.chores;
    }

    async getMembers() {
        if(!this.members) {

        }
        return this.members;
    }
}