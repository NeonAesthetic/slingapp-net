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
  Email VARCHAR(64) NOT NULL UNIQUE,
  FullName VARCHAR(64) NOT NULL,
  PasswordHash VARCHAR(60),
  LoginToken VARCHAR(50),
  TokenGenTime DATETIME,
  LastLogin DATETIME,
  JoinDate DATETIME
);

CREATE INDEX IDX_Account_Email
  ON Accounts(Email);




CREATE TABLE Participants (
  ParticipantID SERIAL,
  RoomID BIGINT UNSIGNED NOT NULL,
  AccountID BIGINT UNSIGNED NULL,
  ScreenName VARCHAR(20) NOT NULL,
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
  RoomCode CHAR(6),
  RoomID BIGINT UNSIGNED NOT NULL,
  CreatedBy BIGINT UNSIGNED NOT NULL,
  ExpirationDate DATETIME,
  RemainingUses INT NULL,
  PRIMARY KEY(RoomCode),
  FOREIGN KEY(RoomID) REFERENCES Rooms(RoomID),
  FOREIGN KEY (CreatedBy) REFERENCES Participants(ParticipantID)
);

# CREATE TABLE Files(
#
# );
#
# CREATE TABLE RoomChat();







