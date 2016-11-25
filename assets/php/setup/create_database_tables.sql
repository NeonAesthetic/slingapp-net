DROP USER 'sling'@'localhost';
DROP DATABASE if exists sling;

CREATE USER 'sling'@'localhost';

CREATE DATABASE sling CHARACTER SET utf8 COLLATE utf8_bin;

GRANT ALL PRIVILEGES ON sling.* TO 'sling';

# Was in accounts?
#FullName VARCHAR(64) NULL,     split fullname to conform to 1NF
use sling;


SET FOREIGN_KEY_CHECKS = 0;
drop table if exists Rooms;
drop table if exists Accounts;
drop table if exists Participants;
drop table if exists Resources;
drop table if exists RoomCodes;
drop table if exists RoomChat;
drop table if exists Files;
drop table if exists RoomChat;
drop table if exists MimeTypes;
drop table if exists Logs;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Rooms (
  RoomID SERIAL,
  RoomName VARCHAR(32) NOT NULL,
  Active BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (RoomID)
);

CREATE TABLE Accounts (
  AccountID SERIAL,
  Email VARCHAR(64) NULL UNIQUE,
  FirstName VARCHAR(32) NULL,
  LastName VARCHAR(32) NULL,
  PasswordHash CHAR(60) BINARY NULL,
  LoginToken CHAR(50) NOT NULL,
  TokenGenTime DATETIME,
  LastLogin DATETIME,
  JoinDate DATETIME,

  PRIMARY KEY (AccountID)
);

CREATE INDEX IDX_Account_Email
  ON Accounts(Email);
# ADD Active Boolean to allow for participants to be classified as dormant without a current user
CREATE TABLE Participants (
  ParticipantID SERIAL,
  RoomID BIGINT UNSIGNED NOT NULL,
  AccountID BIGINT UNSIGNED NULL,
  ScreenName VARCHAR(20) NULL,
  Active BOOLEAN DEFAULT TRUE,

  PRIMARY KEY (ParticipantID),
  FOREIGN KEY(RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
);

CREATE TABLE Resources (
  ResourceID SERIAL,
  ParticipantID BIGINT UNSIGNED NOT NULL,
  RoomID BIGINT UNSIGNED NOT NULL,
  Location VARCHAR(32) UNIQUE,
  TypeID CHAR NOT NULL,
  PRIMARY KEY (ResourceID),
  FOREIGN KEY (ParticipantID) REFERENCES Participants(ParticipantID),
  FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID)
);


CREATE TABLE RoomCodes (
  RoomCode CHAR(6),
  RoomID BIGINT UNSIGNED NOT NULL,
  CreatedBy BIGINT UNSIGNED NOT NULL,
  ExpirationDate DATETIME NULL,
  RemainingUses INT NULL,
  PRIMARY KEY(RoomCode),
  FOREIGN KEY(RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (CreatedBy) REFERENCES Participants(ParticipantID)
);


CREATE TABLE MimeTypes(
  TypeID SERIAL,
  MimeType VARCHAR(64),
  PRIMARY KEY (TypeID)
);

CREATE TABLE Files(
FileID SERIAL,
Data MEDIUMBLOB,
Filename VARCHAR(64),
TypeID BIGINT UNSIGNED NOT NULL,
PRIMARY KEY (FileID),
FOREIGN KEY (TypeID) REFERENCES MimeTypes(TypeID)
);

CREATE TABLE RoomChat(
  RoomChatID  SERIAL,
  RoomID  BIGINT  UNSIGNED NOT NULL,
  ParticipantID BIGINT UNSIGNED NOT NULL,
  Message VARCHAR(400) NULL,
  FileID BIGINT UNSIGNED NULL ,
  SentTime  TIMESTAMP NOT NULL,
  PRIMARY KEY(RoomChatID),
  FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (ParticipantID) REFERENCES Participants (ParticipantID),
  FOREIGN KEY (FileID) REFERENCES Files(FileID)
);

CREATE TABLE Logs(
  Time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  IP CHAR(15),
  File VARCHAR(64),
  Action VARCHAR(64),
  Description VARCHAR(256)
);

CREATE TABLE Animals(
  AnimalID SERIAL,
  Name VARCHAR(32),
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
      ('Harambe')








