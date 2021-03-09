
-- All credential necessary for user authorization
DROP TABLE User;
CREATE TABLE User (
    ID INTEGER,
    Email varchar(320) UNIQUE,
    Name varchar(256) UNIQUE,
    Password varchar(256),
    CreationTime TIMESTAMP,
    Verified BOOLEAN DEFAULT 0,
    PRIMARY KEY(ID)
);

DROP TABLE UserGroup;
CREATE TABLE UserGroup (
    ID INTEGER,
    AccountID int,
    GroupID int,
    PRIMARY KEY (ID),
    FOREIGN KEY (AccountID) REFERENCES User(ID) ON DELETE CASCADE,
    FOREIGN KEY (GroupID) REFERENCES "Group"(ID) ON DELETE CASCADE
);

DROP TABLE "Group";
CREATE TABLE "Group" (
    ID INTEGER,
    Name varchar(256),
    OwnerID int,
    PRIMARY KEY (ID)
);

DROP TABLE Task;
CREATE TABLE Task (
    ID INTEGER,
    GroupID int,
    Name varchar(256),
    Colour varchar(64),
    Desc varchar(1024),
    Completed bool,
    CreationTime DATETIME,
    Deadline DATETIME,
    PRIMARY KEY (ID),
    FOREIGN KEY (GroupID) REFERENCES "Group"(ID) ON DELETE CASCADE
);

DROP TABLE Assigned;
CREATE TABLE Assigned
(
    ID      INTEGER,
    TaskID int,
    UserID  int,
    PRIMARY KEY (ID),
    FOREIGN KEY (TaskID) REFERENCES Task (ID) ON DELETE CASCADE,
    FOREIGN KEY (UserID) REFERENCES User (ID) ON DELETE CASCADE
);

DROP TABLE Invitation;
CREATE TABLE Invitation (
    ID INTEGER,
    UserID int,
    GroupID int,
    PRIMARY KEY (ID),
    FOREIGN KEY (UserID) REFERENCES User (ID) ON DELETE CASCADE,
    FOREIGN KEY (GroupID) REFERENCES "Group"(ID) ON DELETE CASCADE
)