CREATE DATABASE IF NOT EXISTS flowspace_db;
USE flowspace_db;
CREATE TABLE IF NOT EXISTS users(
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(120) UNIQUE NOT NULL,
 password VARCHAR(255) NOT NULL,
 plan VARCHAR(50) DEFAULT 'Starter',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS tasks(
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(200) NOT NULL,
 description TEXT,
 priority VARCHAR(30) DEFAULT 'Medium',
 status VARCHAR(30) DEFAULT 'To Do',
 deadline DATE NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS notes(
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(200) NOT NULL,
 content TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO users(name,email,password,plan) VALUES('Admin User','admin@flowspace.com','$2y$10$D5tjzAc3f.dfvL5ljNaAeOwWDbtchVpQWPzZMg2nLw69QQmCc7lC2','Team') ON DUPLICATE KEY UPDATE email=email;
INSERT INTO tasks(title,description,priority,status) VALUES('Prepare viva explanation','Write project intro and module explanation','High','To Do'),('Design dashboard','Create ClickUp-style dashboard cards','High','Done'),('Connect MySQL','Import SQL database in phpMyAdmin','Medium','In Progress');
INSERT INTO notes(title,content) VALUES('Viva Point','FlowSpace uses frontend + backend + database with a modern productivity UI.'),('Tech Stack','HTML, CSS, JavaScript, jQuery, Basic React, PHP and MySQL.');
