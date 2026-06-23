-- Book Reservation System Database Schema
-- Database: ca


CREATE TABLE IF NOT EXISTS users (
    Username VARCHAR(50) PRIMARY KEY,
    Password VARCHAR(255) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    Surname VARCHAR(50) NOT NULL,
    AddressLine VARCHAR(100) NOT NULL,
    AddressLineTown VARCHAR(100) NOT NULL,
    City VARCHAR(50),
    Telephone VARCHAR(20),
    Mobile VARCHAR(10) NOT NULL
);


CREATE TABLE IF NOT EXISTS categories (
    CategoryID INT PRIMARY KEY,
    CategoryDepartment VARCHAR(100) NOT NULL
);


CREATE TABLE IF NOT EXISTS books (
    ISBN VARCHAR(20) PRIMARY KEY,
    BookTitle VARCHAR(255) NOT NULL,
    Author VARCHAR(255) NOT NULL,
    Edition INT,
    Year INT,
    Category INT,
    Reserved CHAR(1) DEFAULT 'N',
    FOREIGN KEY (Category) REFERENCES categories(CategoryID)
);


CREATE TABLE IF NOT EXISTS reservations (
    ISBN VARCHAR(20) NOT NULL,
    Username VARCHAR(50) NOT NULL,
    ReservedDate DATE NOT NULL,
    PRIMARY KEY (ISBN, Username),
    FOREIGN KEY (ISBN) REFERENCES books(ISBN),
    FOREIGN KEY (Username) REFERENCES users(Username)
);


