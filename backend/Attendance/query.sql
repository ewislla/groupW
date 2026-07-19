CREATE DATABASE IF NOT EXISTS attendance_system;

USE attendance_system;

CREATE TABLE faculties (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    faculty_id INT NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculties (faculty_id) ON DELETE CASCADE ON UPDATE CASCADE
);

SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Empty the table
TRUNCATE TABLE faculties;

-- Step 3: Turn checks back on immediately
SET FOREIGN_KEY_CHECKS = 1;

TRUNCATE TABLE departments;

CREATE TABLE Students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    matric_no VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    faculty_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments (department_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculties (faculty_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Moderator') NOT NULL,
    tokens VARCHAR(255) NOT NULL,
    active ENUM('True', 'false') NOT NULL
) 

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;


ALTER TABLE users 
ADD COLUMN otp_code VARCHAR(10) NULL AFTER password,
ADD COLUMN otp_expiry DATETIME NULL AFTER otp_code;


ALTER TABLE users 
ADD COLUMN current_device_id VARCHAR(100) NULL AFTER tokens;
