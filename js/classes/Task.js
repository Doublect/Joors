export default class Task {
    constructor(ID, GroupID, Name, Desc, Frequency, Length, Completed, CreationTime, Next, Assigned) {
        this.ID = ID;
        this.GroupID = GroupID;
        this.Name = Name;
        this.Desc = Desc;
        this.Frequency = Frequency;
        this.Length = Length;
        this.Completed = Completed;
        this.Next = Next;
        this.Assigned = Assigned;
    }
}