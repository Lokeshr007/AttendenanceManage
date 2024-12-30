-- Create the database
CREATE DATABASE attendance_system;

-- Use the created database
USE attendance_system;

-- Drop existing tables if they exist to start fresh
DROP TABLE IF EXISTS Attendance;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS Classes;
DROP TABLE IF EXISTS Departments;
DROP TABLE IF EXISTS Users;

-- Create the Departments table
CREATE TABLE Departments (
    DepartmentID INT AUTO_INCREMENT PRIMARY KEY,
    DepartmentName VARCHAR(255) NOT NULL
);

-- Create the Classes table
CREATE TABLE Classes (
    ClassID INT AUTO_INCREMENT PRIMARY KEY,
    ClassName VARCHAR(255) NOT NULL,
    DepartmentID INT,
    FOREIGN KEY (DepartmentID) REFERENCES Departments(DepartmentID) ON DELETE CASCADE
);

-- Create the Students table
CREATE TABLE Students (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    StudentName VARCHAR(255) NOT NULL,
    RollNo VARCHAR(50) NOT NULL,
    Type ENUM('DS', 'HS') NOT NULL,
    ClassID INT,
    DepartmentID INT,
    FOREIGN KEY (ClassID) REFERENCES Classes(ClassID) ON DELETE CASCADE,
    FOREIGN KEY (DepartmentID) REFERENCES Departments(DepartmentID) ON DELETE CASCADE
);

-- Create the Users table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('adviser', 'hod', 'principal') NOT NULL,
    DepartmentID INT,
    FOREIGN KEY (DepartmentID) REFERENCES Departments(DepartmentID) ON DELETE SET NULL
);

-- Create the Attendance table
CREATE TABLE Attendance (
    AttendanceID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT,
    DepartmentID INT,
    Date DATE,
    Status ENUM('Present', 'Absent', 'On Duty') NOT NULL,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (DepartmentID) REFERENCES Departments(DepartmentID) ON DELETE CASCADE,
    UNIQUE (StudentID, Date)
);

-- Insert sample data into Departments
INSERT INTO Departments (DepartmentName) VALUES
('IT Department'),
('CSE Department');

-- Insert sample data into Classes
INSERT INTO Classes (ClassName, DepartmentID) VALUES
('2D IT-A', 1),
('3D IT-B', 1),
('3D IT-A', 1),
('4th IT', 1),
('CSE Class 1', 2),
('CSE Class 2', 2);

-- Insert sample data into Students
INSERT INTO Students (StudentName, RollNo, Type, ClassID, DepartmentID) VALUES
('Student 1', '001', 'DS', 1, 1),
('Student 2', '002', 'HS', 1, 1),
('Student 3', '003', 'DS', 2, 1),
('Student 4', '004', 'HS', 2, 1),
('Student 5', '005', 'DS', 5, 2),
('Student 6', '006', 'HS', 6, 2);

-- Insert sample data into Users
INSERT INTO Users (UserName, Password, Role, DepartmentID) VALUES
('abinaya', 'password1', 'adviser', 1),
('prasath', 'password2', 'adviser', 1),
('tamilvanan', 'password3', 'adviser', 1),
('amala', 'password4', 'adviser', 1),
('kali', 'password5', 'adviser', 2),
('uma', 'password6', 'adviser', 2),
('smith', 'password7', 'adviser', 2),
('vadugunadhan', 'password8', 'adviser', 2),
('principal1', 'principalpassword', 'principal', NULL),
('hod1', 'hodpassword', 'hod', 1),
('hod2', 'hodpassword', 'hod', 2);

-- Insert sample data into Attendance
INSERT INTO Attendance (StudentID, DepartmentID, Date, Status) VALUES
(1, 1, '2024-12-30', 'Present'),
(2, 1, '2024-12-30', 'Absent'),
(3, 1, '2024-12-30', 'On Duty'),
(4, 1, '2024-12-30', 'Present'),
(5, 2, '2024-12-30', 'Absent'),
(6, 2, '2024-12-30', 'Present');
