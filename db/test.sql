INSERT INTO User(ID, Email, Name, Password, CreationTime) VALUES (NULL, 'hunor.liszka@protonmail.com', 'Test', '$2y$10$K4AKMCXiarhpi86cyiuvb.fzKlGv6OX50m4xSHVz8MlhalLmfMQRG', 1613728906); -- TestPass

INSERT INTO UserGroup VALUES (NULL, 1, 1);

INSERT INTO "Group" VALUES (NULL, 'TestGroup');

INSERT INTO Task VALUES (NULL, 1, 'Example Task', 'red', 'This is a chore.', NULL, NULL, NULL);

INSERT INTO Assigned VALUES (NULL, 1, 1);

INSERT INTO Invitation VALUES (NULL, 1, 1);