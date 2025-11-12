-- Scoreboard System Database Schema

CREATE DATABASE IF NOT EXISTS scoreboard_system;
USE scoreboard_system;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scoreboards table (each event/game)
CREATE TABLE IF NOT EXISTS scoreboards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    type VARCHAR(50) NOT NULL, -- sports, quiz, competition, etc.
    status ENUM('active', 'paused', 'completed') DEFAULT 'active',
    show_time BOOLEAN DEFAULT TRUE,
    show_rounds BOOLEAN DEFAULT TRUE,
    current_round INT DEFAULT 1,
    total_rounds INT DEFAULT 1,
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Teams/Players table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scoreboard_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255), -- path to logo image
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scoreboard_id) REFERENCES scoreboards(id) ON DELETE CASCADE
);

-- Scores table
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    scoreboard_id INT NOT NULL,
    round_number INT DEFAULT 1,
    score INT DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (scoreboard_id) REFERENCES scoreboards(id) ON DELETE CASCADE
);

-- Additional fields table (flexible custom fields)
CREATE TABLE IF NOT EXISTS custom_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scoreboard_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'number', 'time') DEFAULT 'text',
    display_order INT DEFAULT 0,
    FOREIGN KEY (scoreboard_id) REFERENCES scoreboards(id) ON DELETE CASCADE
);

-- Custom field values
CREATE TABLE IF NOT EXISTS custom_field_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    field_id INT NOT NULL,
    value TEXT,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES custom_fields(id) ON DELETE CASCADE
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@scoreboard.com');
