CREATE PROCEDURE AddUser( IN vEmail VARCHAR(64),
                          IN vFirstName VARCHAR(32),
                          IN vLastName VARCHAR(32),
                          IN vPasswordHash VARCHAR(60),
                          IN vLoginToken VARCHAR(50),
                          IN vTokenGenTime DATETIME,
                          IN vLastLogin DATETIME,
                          IN vJoinDate DATETIME)
  BEGIN
    INSERT INTO Accounts
    (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)
    VALUES(vEmail, vFirstName, vLastName, vPasswordHash, vLoginToken, vTokenGenTime, vLastLogin, vJoinDate);
  END
  ;

DROP PROCEDURE AnonAnimal;

CREATE PROCEDURE AnonAnimal()
  BEGIN
    SELECT CONCAT('Anonymous ', Name) FROM Animals ORDER BY RAND() LIMIT 1;
  END;
;

CALL AnonAnimal(@dog);

  CALL AddUser('test', 'test1', 'test2', 'phash', 'fdnfjsf', NOW(), NOW(), NOW());