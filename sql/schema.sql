CREATE DATABASE pet_management;

USE pet_management;

CREATE TABLE User (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE Pet (
    petID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    breed VARCHAR(100),
    age INT,
    ownerID INT,
    FOREIGN KEY (ownerID) REFERENCES User(userID) ON DELETE CASCADE
);

CREATE TABLE Veterinarian (
    vetID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    specialty VARCHAR(100)
);

CREATE TABLE MedicalRecord (
    recordID INT AUTO_INCREMENT PRIMARY KEY,
    petID INT,
    details TEXT,
    date DATETIME,
    FOREIGN KEY (petID) REFERENCES Pet(petID) ON DELETE CASCADE
);

CREATE TABLE Appointment (
    appointmentID INT AUTO_INCREMENT PRIMARY KEY,
    petID INT,
    vetID INT,
    date DATETIME,
    status VARCHAR(20),
    FOREIGN KEY (petID) REFERENCES Pet(petID),
    FOREIGN KEY (vetID) REFERENCES Veterinarian(vetID)
);

CREATE TABLE Prescription (
    prescriptionID INT AUTO_INCREMENT PRIMARY KEY,
    recordID INT,
    medication VARCHAR(255),
    dosage VARCHAR(255),
    FOREIGN KEY (recordID) REFERENCES MedicalRecord(recordID)
);

-- Insert sample user
INSERT INTO User (username, email, password) VALUES
('Alice', 'alice@example.com', '$2y$10$abcdefghijklmnopqrstuvwx'); -- Replace with a hashed password
