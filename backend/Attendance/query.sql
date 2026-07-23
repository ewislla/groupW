-- Active: 1781179005040@@127.0.0.1@3306@attendance_system
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


ALTER TABLE users
MODIFY COLUMN active ENUM('true', 'false') NOT NULL;


DROP TABLE IF EXISTS students;

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    matric_no VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    level ENUM('100', '200', '300', '400', '500', '600') NOT NULL, 
    current_device_id VARCHAR(100) NULL, 
    is_active ENUM('True', 'False') DEFAULT 'True',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE students 
ADD COLUMN otp_code VARCHAR(10) NULL AFTER email,
ADD COLUMN otp_expiry DATETIME NULL AFTER otp_code;
ALTER TABLE students
ADD COLUMN middle_name VARCHAR(100) NULL AFTER last_name,
ADD COLUMN gender VARCHAR(10) NOT NULL AFTER middle_name;
ALTER TABLE students
ADD COLUMN department_id INT NOT NULL,
ADD COLUMN faculty_id INT NOT NULL;

CREATE TABLE attendance_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL, 
    class_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    latitude DECIMAL(10, 8) NULL,  -- The fixed GPS location of the classroom
    longitude DECIMAL(11, 8) NULL, -- The fixed GPS location of the classroom
    is_active ENUM('True', 'False') DEFAULT 'True',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE attendance_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    student_lat DECIMAL(10, 8) NULL, 
    student_long DECIMAL(11, 8) NULL, 
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_attendance (session_id, student_id),
    
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);


ALTER TABLE students
ADD COLUMN middle_name VARCHAR(100) NULL AFTER last_name,
ADD COLUMN gender VARCHAR(10) NOT NULL AFTER middle_name,
ADD COLUMN current_device_id VARCHAR(255) NULL AFTER level;