'use strict';

export class User
{
    constructor(ID, Email, Name, Password, CreationTime) {
        this.ID = ID;
        this.Email = Email;
        this.Name = Name;
        this.Password = Password;
        this.CreationTime = CreationTime;
    }
}