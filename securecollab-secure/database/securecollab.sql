-- SecureCollab Database Schema and Sample Data
-- For use with XAMPP MySQL / phpMyAdmin

CREATE DATABASE IF NOT EXISTS `securecollab` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `securecollab`;

-- Drop tables if they exist (for clean re-import)
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `files`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `discussions`;
DROP TABLE IF EXISTS `project_members`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `users`;

-- Table: users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: projects
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'General',
  `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: project_members
CREATE TABLE `project_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` VARCHAR(50) DEFAULT 'Member',
  `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_project_user` (`project_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: discussions
CREATE TABLE `discussions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: comments
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `discussion_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`discussion_id`) REFERENCES `discussions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: files
CREATE TABLE `files` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `filesize` INT NOT NULL,
  `filetype` VARCHAR(100) NOT NULL,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: activity_logs
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Users
-- Password for all seed users is: SecureDemo!2026
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrator', 'admin@securecollab.local', '$2y$10$08A8eNmeeNJSltcEW2TpOuIsFkTZgRvHVHznf7CTT23ZD1BbwvzFK', 'admin'),
(2, 'Alex Johnson', 'user@securecollab.local', '$2y$10$08A8eNmeeNJSltcEW2TpOuIsFkTZgRvHVHznf7CTT23ZD1BbwvzFK', 'user'),
(3, 'Sarah Connor', 'sarah@securecollab.local', '$2y$10$08A8eNmeeNJSltcEW2TpOuIsFkTZgRvHVHznf7CTT23ZD1BbwvzFK', 'user');

-- Seed Projects
INSERT INTO `projects` (`id`, `title`, `description`, `category`, `status`, `created_by`) VALUES
(1, 'Enterprise Cloud Migration', 'Migrating core legacy infrastructure to AWS multi-region cluster with high availability.', 'Infrastructure', 'active', 1),
(2, 'Cybersecurity Audit 2026', 'Comprehensive web vulnerability review, penetration testing, and compliance verification.', 'Security', 'active', 1),
(3, 'Mobile Banking Redesign', 'Designing responsive Flutter mobile client UI and secure REST API backend.', 'Mobile Dev', 'active', 2);

-- Seed Project Members
INSERT INTO `project_members` (`project_id`, `user_id`, `role`) VALUES
(1, 1, 'Owner'),
(1, 2, 'Developer'),
(1, 3, 'DevOps Engineer'),
(2, 1, 'Lead Auditor'),
(2, 2, 'Security Tester'),
(3, 2, 'Product Manager'),
(3, 3, 'UI/UX Designer');

-- Seed Discussions
INSERT INTO `discussions` (`id`, `project_id`, `user_id`, `title`, `content`) VALUES
(1, 1, 1, 'Terraform Module Architecture', 'Let\'s align on the modular structure for the Terraform IAC scripts for AWS VPC deployment.'),
(2, 2, 1, 'Web Application Security Standard Checklist', 'Reviewing OWASP Top 10 vulnerabilities including XSS, CSRF, and Frame protection mechanisms.'),
(3, 3, 2, 'API Authentication Flow Proposal', 'Proposing OAuth2 JWT tokens with short expiry and refresh token rotation.');

-- Seed Comments
INSERT INTO `comments` (`id`, `discussion_id`, `user_id`, `comment`) VALUES
(1, 1, 2, 'I have drafted the VPC subnet modules in the staging repository.'),
(2, 2, 2, 'Initial scan completed. We should verify all input fields and HTTP headers across services.'),
(3, 2, 3, 'Agreed! Ensure we test reflected and stored parameters thoroughly.');

-- Seed Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'User Login', 'Admin logged in successfully', '127.0.0.1'),
(1, 'Project Created', 'Created project Enterprise Cloud Migration', '127.0.0.1'),
(2, 'User Login', 'User logged in successfully', '127.0.0.1'),
(2, 'Comment Added', 'Posted comment on Web Application Security Standard Checklist', '127.0.0.1');
