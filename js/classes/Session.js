'use strict';

export default class Session {
    OwnerID;
    SessionKey;

    constructor(OwnerID, SessionKey) {
        this.OwnerID = OwnerID;
        this.SessionKey = SessionKey;
    }
}