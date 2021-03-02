DROP TABLE Session;
CREATE TABLE Session (
     ID INTEGER,
     SessionKey TEXT UNIQUE,
     OwnerID INTEGER UNIQUE,
     ExpiryTime TIMESTAMP,
     PRIMARY KEY(ID)
);