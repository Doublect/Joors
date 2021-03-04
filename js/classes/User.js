'use strict';

export class User
{
    constructor(ID, Email, Username, Password, CreationTime) {
        this.ID = ID;
        this.Email = Email;
        this.Username = Username;
        this.Password = Password;
        this.CreationTime = CreationTime;
    }
}