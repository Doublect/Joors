export default class Task {
    constructor(ID, GroupID, Name, Colour, Desc, Completed, CreationTime, Deadline, Assigned) {
        this.ID = ID;
        this.GroupID = GroupID;
        this.Name = Name;
        this.Colour = Colour;
        this.Desc = Desc;
        this.Completed = Completed;
        this.CreationTime = CreationTime;
        this.Deadline = Deadline;
        this.Assigned = Assigned;
    }
}