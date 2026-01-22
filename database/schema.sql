-- ============================
-- Database: sofeng
-- Project: Alumni Website
-- ============================

CREATE DATABASE IF NOT EXISTS sofeng
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sofeng;

-- ============================
-- Table: user
-- ============================

CREATE TABLE IF NOT EXISTS user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin','student','alumni','event_manager') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO user (email, password_hash, name, role, is_active) VALUES
(
  'amir.hakim@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Amir Hakim',
  'student',
  TRUE
),
(
  'nur.aisyah@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Nur Aisyah',
  'student',
  TRUE
),
(
  'daniel.khairul@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Daniel Khairul',
  'student',
  TRUE
),
(
  'farah.nabila@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Farah Nabila',
  'alumni',
  TRUE
),
(
  'adam.irfan@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Adam Irfan',
  'alumni',
  TRUE
),
(
  'siti.zulaikha@gmail.com',
  '$2y$10$9rY0C9QzQhQk6l8mU2GgX.9lZ7Yl5R0NnXjC6YV8s1jUj1Z7l9e7a',
  'Siti Zulaikha',
  'alumni',
  TRUE
);


CREATE TABLE IF NOT EXISTS profile (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    education TEXT,
    contact_info TEXT,
    publications_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
);


CREATE TABLE career_history (
    career_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,

    job_title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NULL,

    description TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_career_user
        FOREIGN KEY (user_id)
        REFERENCES user(user_id)
        ON DELETE CASCADE
);
