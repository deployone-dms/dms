-- Create login_table for user authentication
CREATE TABLE IF NOT EXISTS `login_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` tinyint(1) NOT NULL COMMENT '1=Admin, 2=User, 3=Supervisor, 4=Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin account (password: admin123)
INSERT INTO `login_table` (`email`, `password`, `account_type`) VALUES
('admin@yakapdaycare.com', 'admin123', 1);
