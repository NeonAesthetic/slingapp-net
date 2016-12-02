# CREATE USER AND DATABASE

DROP USER 'sling'@'localhost';
DROP DATABASE IF EXISTS sling;

CREATE USER 'sling'@'localhost';

CREATE DATABASE sling
  CHARACTER SET utf8
  COLLATE utf8_bin;

GRANT ALL PRIVILEGES ON sling.* TO 'sling';

# Was in accounts?
#FullName VARCHAR(64) NULL,     split fullname to conform to 1NF
USE sling;




#DROP ALL TABLES
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Rooms;
DROP TABLE IF EXISTS Accounts;
DROP TABLE IF EXISTS Participants;
DROP TABLE IF EXISTS Resources;
DROP TABLE IF EXISTS RoomCodes;
DROP TABLE IF EXISTS RoomChat;
DROP TABLE IF EXISTS Files;
DROP TABLE IF EXISTS RoomChat;
DROP TABLE IF EXISTS MimeTypes;
DROP TABLE IF EXISTS Logs;
DROP TABLE IF EXISTS Animals;
DROP TABLE IF EXISTS RoomAccount;
SET FOREIGN_KEY_CHECKS = 1;


#======================START CREATE TABLES ================================
CREATE TABLE Rooms (
  RoomID   BIGINT UNSIGNED,
  RoomName VARCHAR(32) NOT NULL,
  Active   BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (RoomID)
);

CREATE TABLE Accounts (
  AccountID    BIGINT UNSIGNED NOT NULL,
  Email        VARCHAR(64)     NULL UNIQUE,
  FirstName    VARCHAR(32)     NULL,
  LastName     VARCHAR(32)     NULL,
  PasswordHash CHAR(60) BINARY NULL,
  LoginToken   CHAR(50)        NOT NULL,
  TokenGenTime DATETIME,
  LastLogin    DATETIME,
  JoinDate     DATETIME,
  ScreenName   VARCHAR(20)     NULL,
  Active       BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (AccountID)
);

CREATE INDEX IDX_Account_Email
  ON Accounts (Email);

CREATE TABLE RoomAccount (
  AccountID BIGINT UNSIGNED NOT NULL,
  RoomID    BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (AccountID, RoomID),
  FOREIGN KEY (AccountID) REFERENCES Accounts (AccountID),
  FOREIGN KEY (RoomID) REFERENCES Rooms (RoomID)
);

CREATE TABLE Resources (
  ResourceID SERIAL,
  AccountID  BIGINT UNSIGNED NOT NULL,
  RoomID     BIGINT UNSIGNED NOT NULL,
  Location   VARCHAR(32) UNIQUE,
  TypeID     CHAR            NOT NULL,
  PRIMARY KEY (ResourceID),
  FOREIGN KEY (AccountID) REFERENCES Accounts (AccountID),
  FOREIGN KEY (RoomID) REFERENCES Rooms (RoomID)
);


CREATE TABLE RoomCodes (
  RoomCode       CHAR(6),
  RoomID         BIGINT UNSIGNED NOT NULL,
  CreatedBy      BIGINT UNSIGNED NOT NULL,
  ExpirationDate DATETIME        NULL,
  RemainingUses  INT             NULL,
  PRIMARY KEY (RoomCode),
  FOREIGN KEY (RoomID) REFERENCES Rooms (RoomID),
  FOREIGN KEY (CreatedBy) REFERENCES Accounts (AccountID)
);


CREATE TABLE MimeTypes (
  TypeID   SERIAL,
  MimeType VARCHAR(64),
  PRIMARY KEY (TypeID)
);

CREATE TABLE Files (
  FileID   SERIAL,
  Data     MEDIUMBLOB,
  Filename VARCHAR(64),
  TypeID   BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (FileID),
  FOREIGN KEY (TypeID) REFERENCES MimeTypes (TypeID)
);

CREATE TABLE Messages (
  MessageID     BIGINT UNSIGNED,
  RoomID        BIGINT UNSIGNED NOT NULL,
  AccountID     BIGINT UNSIGNED NOT NULL,
  Content       VARCHAR(2000) NULL,
  FileID        BIGINT UNSIGNED NULL,
  SentTime      TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (MessageID),
  FOREIGN KEY (RoomID) REFERENCES Rooms (RoomID),
  FOREIGN KEY (AccountID) REFERENCES Accounts (AccountID),
  FOREIGN KEY (FileID) REFERENCES Files (FileID)
);

CREATE TABLE Logs (
  Time        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  IP          CHAR(15),
  AccountID   BIGINT UNSIGNED,
  RoomID      BIGINT UNSIGNED,
  File        VARCHAR(64),
  Action      VARCHAR(64),
  Description VARCHAR(256)
);

CREATE INDEX IDX_Logs_AccountID ON Logs(AccountID);
CREATE INDEX IDX_Logs_RoomID ON Logs(RoomID);

CREATE TABLE Animals (
  AnimalID SERIAL,
  Name     VARCHAR(32),
  PRIMARY KEY (AnimalID)
);

INSERT INTO Animals (Name)
VALUES
  ('Panda'),
  ('Zebra'),
  ('Elephant'),
  ('Moose'),
  ('Canine'),
  ('Feline'),
  ('Seal'),
  ('Snake'),
  ('Orangutan'),
  ('Lion'),
  ('Tiger'),
  ('Water Buffalo'),
  ('Chameleon'),
  ('Frog'),
  ('Gecko'),
  ('Bear'),
  ('Sloth'),
  ('Crocodile'),
  ('Alligator'),
  ('Duck'),
  ('Falcon'),
  ('Squirrel'),
  ('Lizard'),
  ('Crow'),
  ('Penguin'),
  ('Slow Loris'),
  ('Sandpiper'),
  ('Skunk'),
  ('Deer'),
  ('Fox'),
  ('Turtle'),
  ('Harambe');


#===================END CREATE TABLES====================



#===================START FUNCTIONS/PROCEDURES============

