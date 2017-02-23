SELECT * FROM Rooms
JOIN Participants
ON Rooms.RoomID = Participants.RoomID
JOIN Accounts
ON Participants.AccountID = Accounts.AccountID