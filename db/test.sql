INSERT INTO User(ID, Email, Name, Password, CreationTime) VALUES (NULL, 'hunor.liszka@protonmail.com', 'Test', '$2y$10$K4AKMCXiarhpi86cyiuvb.fzKlGv6OX50m4xSHVz8MlhalLmfMQRG', 1613728906); -- TestPass

INSERT INTO UserGroup VALUES (NULL, 1, 1, 0);
INSERT INTO UserGroup VALUES (NULL, 1, 2, 0);

INSERT INTO "Group" VALUES (NULL, 'TestGroup', 1);
INSERT INTO "Group" VALUES (NULL, 'TestGroup2', 1);

INSERT INTO Task VALUES (NULL, 1, 'Example Task', 'This is a chore.', 'daily', 1, 20, 0, NULL, NULL);
INSERT INTO Task VALUES (NULL, 1, 'Example Task2', 'This is A chore.', 'weekly', 1, 120, 0, NULL, NULL);
INSERT INTO Task VALUES (NULL, 1, 'Example Task3', 'This is AA chore.', 'monthly', 1, 550, 0, NULL, NULL);

INSERT INTO Task VALUES (NULL, 2, 'Example Task', 'This is a chore.', 'daily', 1, 20, 0, NULL, NULL);
INSERT INTO Task VALUES (NULL, 2, 'Example Task2', 'This is A chore.', 'weekly', 1, 120, 0, NULL, NULL);