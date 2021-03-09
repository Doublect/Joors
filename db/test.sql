INSERT INTO User(ID, Email, Name, Password, CreationTime) VALUES (NULL, 'hunor.liszka@protonmail.com', 'Test', '$2y$10$K4AKMCXiarhpi86cyiuvb.fzKlGv6OX50m4xSHVz8MlhalLmfMQRG', 1613728906); -- TestPass

INSERT INTO UserGroup VALUES (NULL, 1, 1);
INSERT INTO UserGroup VALUES (NULL, 1, 2);

INSERT INTO "Group" VALUES (NULL, 'TestGroup', 1);
INSERT INTO "Group" VALUES (NULL, 'TestGroup2', 1);

INSERT INTO Task VALUES (NULL, 1, 'Example Task', 'red', 'This is a chore.', NULL, NULL, NULL);
INSERT INTO Task VALUES (NULL, 1, 'Example Task2', 'red', 'This is a chore.', NULL, NULL, NULL);
INSERT INTO Task VALUES (NULL, 1, 'Example Task3', 'red', 'This is a chore.', NULL, NULL, NULL);

INSERT INTO Task VALUES (NULL, 2, 'Example Task', 'red', 'This is a chore.', NULL, NULL, NULL);
INSERT INTO Task VALUES (NULL, 2, 'Example Task2', 'red', 'This is a chore.', NULL, NULL, NULL);

INSERT INTO Assigned VALUES (NULL, 1, 1);
INSERT INTO Assigned VALUES (NULL, 2, 1);
INSERT INTO Assigned VALUES (NULL, 3, 1);
INSERT INTO Assigned VALUES (NULL, 4, 1);
INSERT INTO Assigned VALUES (NULL, 5, 1);

INSERT INTO Invitation VALUES (NULL, 1, 1);