-- escape_db.sql

CREATE DATABASE IF NOT EXISTS escape_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE escape_db;

CREATE TABLE users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  username        VARCHAR(50) UNIQUE NOT NULL,
  email           VARCHAR(100) UNIQUE NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  avatar          VARCHAR(10) DEFAULT '🧙',
  role            ENUM('player','admin') DEFAULT 'player',
  total_points    INT DEFAULT 0,
  total_games     INT DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE game_sessions (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  level_id        INT NOT NULL,
  status          ENUM('in_progress','completed','failed','abandoned') DEFAULT 'in_progress',
  current_node    VARCHAR(50) DEFAULT 'node_start',   -- ★ position joueur
  time_elapsed    INT DEFAULT 0,
  hints_used      INT DEFAULT 0,
  score           INT DEFAULT 0,
  inventory       JSON,                                -- objets portés
  puzzle_states   JSON,                               -- puzzles résolus
  visited_nodes   JSON,                               -- nodes explorés
  started_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  finished_at     TIMESTAMP NULL,
  UNIQUE KEY uq_user_level_active (user_id, level_id, status),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_progress (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  level_id        INT NOT NULL,
  best_time       INT,
  best_score      INT,
  stars           TINYINT DEFAULT 0,                  -- 1 à 3 étoiles
  completed_at    TIMESTAMP,
  UNIQUE KEY uq_progress (user_id, level_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE leaderboard (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  level_id        INT NOT NULL,
  score           INT NOT NULL,
  time_elapsed    INT NOT NULL,
  hints_used      INT DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin user insertion
INSERT INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@gmail.com', '$2y$10$hOnkltJbHPRWfJbkN53z2O70fW6BlOapBvd4mUa2x2ccuwgZGUq86', 'admin');
