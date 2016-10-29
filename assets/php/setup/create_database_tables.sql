use sling;


SET FOREIGN_KEY_CHECKS = 0;
drop table if exists Rooms;
drop table if exists Accounts;
drop table if exists Participants;
drop table if exists Resources;
drop table if exists RoomCodes;
drop table if exists RoomChat;
drop table if exists Files;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Rooms (
  RoomID SERIAL,
  RoomName VARCHAR(32) NOT NULL,
  PRIMARY KEY (RoomID)
);

CREATE TABLE Accounts (
  AccountID SERIAL,
  Username VARCHAR(32) NOT NULL UNIQUE,
  Email VARCHAR(64) NOT NULL,
  FullName VARCHAR(64) NOT NULL,
  PasswordHash VARCHAR(60),
  LoginToken VARCHAR(50),
  TokenGenTime DATETIME,
  LastLogin DATETIME,
  JoinDate DATETIME
);


CREATE TABLE Participants (
  ParticipantID SERIAL,
  RoomID BIGINT UNSIGNED NOT NULL,
  AccountID BIGINT UNSIGNED NULL,
  #Should we change from Username to Alias to prevent confusion with Username in Accounts?
  #Should we change VARCHAR length from 20 to 32? Username in Accounts is length 32
  Username VARCHAR(20) NOT NULL,
  LoginToken VARCHAR(50) NULL,
  FingerPrint VARCHAR(50),

  PRIMARY KEY (ParticipantID),
  FOREIGN KEY(RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
);

CREATE TABLE Resources (
  ResourceID SERIAL,
  ParticipantID BIGINT UNSIGNED NOT NULL,
  RoomID BIGINT UNSIGNED NOT NULL,
  Location VARCHAR(32) UNIQUE,
  TypeID BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (ResourceID),
  FOREIGN KEY (ParticipantID) REFERENCES Participants(ParticipantID),
  FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID)
);


CREATE TABLE RoomCodes (
  RoomCode CHAR(8), #changed VARCHAR to CHAR since its always going to be a length of 8 characters
  RoomID BIGINT UNSIGNED NOT NULL,
  CreatedBy BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY(RoomCode),
  FOREIGN KEY(RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (CreatedBy) REFERENCES Participants(ParticipantID)
);

CREATE TABLE Files();

CREATE TABLE RoomChat();







