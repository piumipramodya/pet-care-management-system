-- Create the database
CREATE DATABASE pet_management;

-- Use the database
USE pet_management;

-- Create User table
CREATE TABLE User (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Create Pet table
CREATE TABLE Pet (
    petID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    ownerID INT NOT NULL,
    FOREIGN KEY (ownerID) REFERENCES User(userID) ON DELETE CASCADE
);

-- Create Veterinarian table
CREATE TABLE Veterinarian (
    vetID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL
);

-- Create MedicalRecord table
CREATE TABLE MedicalRecord (
    recordID INT AUTO_INCREMENT PRIMARY KEY,
    petID INT NOT NULL,
    details TEXT NOT NULL,
    date DATETIME NOT NULL,
    FOREIGN KEY (petID) REFERENCES Pet(petID) ON DELETE CASCADE
);

-- Create Appointment table
CREATE TABLE Appointment (
    appointmentID INT AUTO_INCREMENT PRIMARY KEY,
    petID INT NOT NULL,
    vetID INT NOT NULL,
    date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'available',
    FOREIGN KEY (petID) REFERENCES Pet(petID) ON DELETE CASCADE,
    FOREIGN KEY (vetID) REFERENCES Veterinarian(vetID) ON DELETE CASCADE
);

-- Create Prescription table
CREATE TABLE Prescription (
    prescriptionID INT AUTO_INCREMENT PRIMARY KEY,
    recordID INT NOT NULL,
    medication VARCHAR(255) NOT NULL,
    dosage VARCHAR(255) NOT NULL,
    FOREIGN KEY (recordID) REFERENCES MedicalRecord(recordID) ON DELETE CASCADE
);

-- Insert sample data into User table
INSERT INTO User (username, email, password) VALUES
('Alice', 'alice@example.com', '$2y$10$abcdefghijklmnopqrstuvwx'), -- Hashed password
('Bob', 'bob@example.com', '$2y$10$abcdefghijklmnopqrstuvwx');    -- Replace with real hashed passwords

-- Insert sample data into Veterinarian table
INSERT INTO Veterinarian (name, specialty) VALUES
('Dr. Johnson', 'General Medicine'),
('Dr. Smith', 'Surgery');

-- Insert sample data into Pet table
INSERT INTO Pet (name, breed, age, ownerID) VALUES
('Buddy', 'Golden Retriever', 3, 1),
('Charlie', 'Labrador', 5, 1),
('Bella', 'Poodle', 2, 2);

-- Insert sample data into MedicalRecord table
INSERT INTO MedicalRecord (petID, details, date) VALUES
(1, 'Routine check-up. Healthy.', '2025-01-15 10:00:00'),
(2, 'Vaccination: Rabies shot.', '2025-02-01 14:30:00');

-- Insert sample data into Appointment table
INSERT INTO Appointment (petID, vetID, date, status) VALUES
(1, 1, '2025-02-15 09:00:00', 'booked'),
(2, 2, '2025-02-16 11:00:00', 'booked'),
(3, 1, '2025-02-17 10:00:00', 'available');

-- Insert sample data into Prescription table
INSERT INTO Prescription (recordID, medication, dosage) VALUES
(1, 'Vitamin supplements', '1 tablet daily'),
(2, 'Rabies vaccine', 'Administered once');

UPDATE User SET password = '$2b$12$IB.CXGfRP0mmAs/6YfydEu5xWVb137TqdlnLooiast4LwDstsQ69K' WHERE email = 'alice@example.com';
INSERT INTO Pet (name, breed, age, ownerID) VALUES
('Buddy', 'Golden Retriever', 3, 1),
('Charlie', 'Labrador', 5, 1);

INSERT INTO MedicalRecord (petID, details, date) VALUES
(1, 'Vaccinated for rabies', '2023-01-15'),
(1, 'Treated for fleas', '2023-06-10'),
(2, 'Routine check-up', '2023-04-20');
SELECT * FROM Pet;
SELECT name, breed, age, ownerID, COUNT(*)
FROM Pet
GROUP BY name, breed, age, ownerID
HAVING COUNT(*) > 1;


CREATE TEMPORARY TABLE TempPet AS
SELECT MIN(petID) AS petID
FROM Pet
GROUP BY name, breed, age, ownerID;


DELETE FROM Pet
WHERE petID NOT IN (SELECT petID FROM TempPet);

SET SQL_SAFE_UPDATES = 0;

DELETE FROM Pet
WHERE petID NOT IN (SELECT petID FROM TempPet);
SET SQL_SAFE_UPDATES = 1;

SELECT * FROM Pet;

SELECT name, breed, age, ownerID, COUNT(*)
FROM Pet
GROUP BY name, breed, age, ownerID
HAVING COUNT(*) > 1;

SELECT * FROM Pet;

SELECT * FROM User;
SELECT * FROM Appointment;

SELECT Appointment.*, Pet.name AS pet_name, Veterinarian.name AS vet_name, Veterinarian.specialty
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN Veterinarian ON Appointment.vetID = Veterinarian.vetID;

SELECT Pet.*, User.username
FROM Pet
JOIN User ON Pet.ownerID = User.userID;

SELECT DISTINCT status FROM Appointment;

UPDATE Appointment
SET status = 'cancelled'
WHERE status = 'available';

SET SQL_SAFE_UPDATES = 0;
UPDATE Appointment
SET status = 'cancelled'
WHERE status = 'available';

SET SQL_SAFE_UPDATES = 1;
SELECT DISTINCT status FROM Appointment;


SELECT 
    Appointment.appointmentID,
    Pet.name AS pet_name,
    Veterinarian.name AS vet_name,
    Appointment.date,
    Appointment.status
FROM 
    Appointment
JOIN 
    Pet ON Appointment.petID = Pet.petID
JOIN 
    Veterinarian ON Appointment.vetID = Veterinarian.vetID
ORDER BY 
    Appointment.date ASC;
SELECT DISTINCT status FROM Appointment;

ALTER TABLE User ADD role ENUM('user', 'veterinarian', 'admin') DEFAULT 'user';
SELECT * FROM User WHERE email = 'dr.johnson@example.com';

UPDATE User 
SET password = '$2y$10$o/wJ1IM2GEZlTM4a68WYquvucoDDvseS1Bfylkx7e7zc/lQfU/FDu', role = 'veterinarian'
WHERE email = 'dr.johnson@example.com';

SELECT userID, username, email, role
FROM User
WHERE role = 'veterinarian';


SELECT Appointment.*, Pet.name AS pet_name, User.username AS owner_name
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User ON Pet.ownerID = User.userID
WHERE Appointment.vetID = 1;

SELECT vetID, name, specialty
FROM Veterinarian;
USE pet_management;
SELECT vetID, name, specialty
FROM Veterinarian;


SELECT * FROM Appointment WHERE vetID = 1;
SELECT * FROM Appointment WHERE status = 'booked';


DELETE Appointment
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
WHERE Pet.ownerID = 1;
SELECT * FROM Appointment;
SELECT userID, username, email FROM User;
SELECT vetID, name, specialty FROM Veterinarian;
DELETE Appointment
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
WHERE Pet.ownerID = 2;
SET SQL_SAFE_UPDATES = 0;
DELETE Appointment
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
WHERE Pet.ownerID = 2;
SET SQL_SAFE_UPDATES = 1;

SELECT appointmentID, petID, vetID, date, status 
FROM Appointment;

use pet_management


SET SQL_SAFE_UPDATES = 0;
DELETE FROM Appointment;
SET SQL_SAFE_UPDATES = 1;
SELECT Appointment.appointmentID, Appointment.date, Appointment.status,
       Pet.name AS pet_name, User.username AS owner_name
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User ON Pet.ownerID = User.userID
WHERE Appointment.vetID = 1 AND Appointment.status = 'booked'
ORDER BY Appointment.date ASC;
SELECT DISTINCT status FROM Appointment;

SELECT Appointment.appointmentID, Appointment.date, Appointment.status,
       Pet.name AS pet_name, User.username AS owner_name
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User ON Pet.ownerID = User.userID
WHERE Appointment.vetID = 1 AND Appointment.status = 'booked'
ORDER BY Appointment.date ASC;
SELECT userID, role FROM User WHERE userID = 3;

SELECT * FROM Appointment WHERE vetID = 3;
SELECT userID, email, role FROM User WHERE userID = 1;
SELECT * FROM Appointment WHERE vetID = 1;
SELECT * FROM Appointment WHERE vetID = 1;
SELECT userID, username, email, role FROM User;
SELECT * FROM Veterinarian WHERE userID = 3;
UPDATE User SET role = 'veterinarian' WHERE userID = 3;
SELECT * FROM Veterinarian WHERE userID = 3;

DESCRIBE Veterinarian;
SELECT * FROM Veterinarian;
ALTER TABLE Veterinarian DROP COLUMN userID;




SET SQL_SAFE_UPDATES = 0;
DELETE FROM User WHERE username = 'Dr. Johnson';
SET SQL_SAFE_UPDATES = 1;
SELECT * FROM Veterinarian WHERE vetID = 1;
SELECT * FROM Veterinarian;


INSERT INTO User (userID, username, email, password, role)
VALUES (3, 'Dr. Johnson', 'dr.johnson@example.com', '$2y$10$o/wJ1IM2GEZlTM4a68WYquvucoDDvseS1Bfylkx7e7zc/lQfU/FDu', 'veterinarian');
UPDATE Veterinarian 
SET userID = (SELECT userID FROM User WHERE email = 'dr.johnson@example.com')
WHERE vetID = 1;


SELECT U.userID, U.username, U.email, V.vetID, V.name
FROM User U
JOIN Veterinarian V ON U.userID = V.userID
WHERE U.email = 'dr.johnson@example.com';


SELECT * FROM Appointment WHERE status = 'booked';
use pet_management


SELECT DISTINCT status FROM Appointment;
SELECT 
    Appointment.appointmentID, 
    Pet.name AS pet_name, 
    Owner.username AS owner_name, 
    Veterinarian.name AS vet_name,
    Appointment.date, 
    Appointment.status
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User AS Owner ON Pet.ownerID = Owner.userID
JOIN Veterinarian ON Appointment.vetID = Veterinarian.vetID
WHERE Veterinarian.name LIKE '%Johnson%' 
AND Appointment.status = 'booked';

SELECT 
    Appointment.appointmentID, 
    Pet.name AS pet_name, 
    Owner.username AS owner_name, 
    Veterinarian.name AS vet_name, 
    Appointment.date, 
    Appointment.status
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User AS Owner ON Pet.ownerID = Owner.userID
JOIN Veterinarian ON Appointment.vetID = Veterinarian.vetID
WHERE Veterinarian.name LIKE '%Johnson%';


SELECT vetID, name FROM Veterinarian WHERE name LIKE '%Johnson%';

SELECT 
    Appointment.appointmentID, 
    Pet.name AS pet_name, 
    Owner.username AS owner_name, 
    Veterinarian.name AS vet_name, 
    Appointment.date, 
    Appointment.status
FROM Appointment
JOIN Pet ON Appointment.petID = Pet.petID
JOIN User AS Owner ON Pet.ownerID = Owner.userID
JOIN Veterinarian ON Appointment.vetID = Veterinarian.vetID
WHERE Veterinarian.vetID = 1;

SELECT vetID, name FROM Veterinarian;
SELECT * FROM Appointment WHERE vetID = 1;
SELECT * FROM Appointment WHERE vetID = 1;
SELECT vetID, userID, name FROM Veterinarian WHERE vetID = 1;
SELECT vetID, userID, name FROM Veterinarian WHERE userID = 3;

INSERT INTO Prescription (recordID, medication, dosage) VALUES
(3, 'Rabies booster shot', 'Administered once'),
(4, 'Flea treatment', 'Apply once every 3 months');

SELECT P.prescriptionID, P.medication, P.dosage, MR.recordID, MR.petID, PT.name AS PetName
FROM Prescription P
JOIN MedicalRecord MR ON P.recordID = MR.recordID
JOIN Pet PT ON MR.petID = PT.petID;

SELECT recordID, medication, dosage, COUNT(*) as count
FROM Prescription
GROUP BY recordID, medication, dosage
HAVING COUNT(*) > 1;


DELETE P1 
FROM Prescription P1
JOIN Prescription P2 
ON P1.recordID = P2.recordID 
AND P1.medication = P2.medication 
AND P1.dosage = P2.dosage
AND P1.prescriptionID > P2.prescriptionID;

SET SQL_SAFE_UPDATES = 0;

DELETE P1 
FROM Prescription P1
JOIN Prescription P2 
ON P1.recordID = P2.recordID 
AND P1.medication = P2.medication 
AND P1.dosage = P2.dosage
AND P1.prescriptionID > P2.prescriptionID;

SET SQL_SAFE_UPDATES = 1;

SELECT recordID, medication, dosage, COUNT(*) as count
FROM Prescription
GROUP BY recordID, medication, dosage
HAVING COUNT(*) > 1;




















