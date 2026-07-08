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


