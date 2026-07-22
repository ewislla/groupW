USE group_message_app;

-- Create the Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    api_token VARCHAR(128) UNIQUE,
);

-- Create the Messages Table
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    user_id INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);


-- 1. Wipe all existing data to start fresh
SET FOREIGN_KEY_CHECKS = 0; 
TRUNCATE TABLE messages;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Add the email column to the users table
ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL;

-- 3. Create the new reactions table with utf8mb4 to support emojis
CREATE TABLE reactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    message_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    emoji VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
);


