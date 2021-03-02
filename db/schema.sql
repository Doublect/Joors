
-- All credential necessary for user authorization
DROP TABLE Account;
CREATE TABLE Account (
    ID INTEGER,
    Username varchar(256) UNIQUE,
    Password varchar(512),
    CreationTime TIMESTAMP,
    PRIMARY KEY(ID)
);

DROP TABLE AccountGroup;
CREATE TABLE AccountGroup (
    ID INTEGER,
    AccountID int,
    GroupID int,
    PRIMARY KEY (ID),
    FOREIGN KEY (AccountID) REFERENCES Account(ID) ON DELETE CASCADE,
    FOREIGN KEY (GroupID)   REFERENCES "Group"(ID) ON DELETE CASCADE
);

DROP TABLE "Group";
CREATE TABLE "Group" (
    ID INTEGER,
    Name varchar(256),
    PRIMARY KEY (ID)
);

DROP TABLE Chore;
CREATE TABLE Chore (
    ID INTEGER,
    GroupID int,
    AssignID int,
    Name varchar(256),
    Colour varchar(64),
    Desc varchar(1024),
    Completed bool,
    CreationTime DATETIME,
    Deadline DATETIME,
    PRIMARY KEY (ID),
    FOREIGN KEY (GroupID) REFERENCES "Group"(ID) ON DELETE CASCADE,
    FOREIGN KEY (AssignID) REFERENCES Account(ID) ON DELETE CASCADE
);
