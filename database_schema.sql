-- Book Reservation System Database Schema
-- Run this SQL file to create the database structure

CREATE DATABASE IF NOT EXISTS labdb;
USE labdb;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    mobile VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Book categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- Books table
CREATE TABLE IF NOT EXISTS books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category_id INT,
    edition VARCHAR(50),
    year_published INT,
    is_reserved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    UNIQUE KEY unique_book_reservation (book_id)
);

-- Insert sample categories
INSERT INTO categories (category_name) VALUES
('Fiction'),
('Non-Fiction'),
('Science'),
('Technology'),
('History'),
('Biography'),
('Mystery'),
('Romance'),
('Fantasy'),
('Self-Help');

-- Insert sample books
INSERT INTO books (isbn, title, author, category_id, edition, year_published, is_reserved) VALUES
('978-0-13-468599-1', 'Clean Code', 'Robert C. Martin', 4, '1st', 2008, 0),
('978-0-596-52068-7', 'JavaScript: The Good Parts', 'Douglas Crockford', 4, '1st', 2008, 0),
('978-0-13-235088-4', 'The Clean Coder', 'Robert C. Martin', 4, '1st', 2011, 0),
('978-0-134-68584-7', 'Effective Java', 'Joshua Bloch', 4, '3rd', 2017, 0),
('978-0-321-12521-7', 'Domain-Driven Design', 'Eric Evans', 4, '1st', 2003, 0),
('978-1-59327-599-0', 'Python Crash Course', 'Eric Matthes', 4, '2nd', 2019, 0),
('978-0-7432-7356-5', '1984', 'George Orwell', 1, 'Reprint', 1949, 0),
('978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 1, '50th Anniversary', 1960, 0),
('978-0-553-38016-3', 'A Brief History of Time', 'Stephen Hawking', 3, '1st', 1988, 0),
('978-0-7432-7357-2', 'The Great Gatsby', 'F. Scott Fitzgerald', 1, 'Reissue', 1925, 0),
('978-0-14-028329-3', 'The Origin of Species', 'Charles Darwin', 3, '6th', 1859, 0),
('978-0-307-58837-1', 'Steve Jobs', 'Walter Isaacson', 6, '1st', 2011, 0),
('978-0-316-76948-0', 'The Catcher in the Rye', 'J.D. Salinger', 1, 'Reprint', 1951, 0),
('978-0-06-093546-7', 'The Alchemist', 'Paulo Coelho', 1, '25th Anniversary', 1988, 0),
('978-0-345-53934-4', 'Sapiens', 'Yuval Noah Harari', 5, '1st', 2011, 0);
