-- MySQL schema for Prayer Wall
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(80) DEFAULT NULL,
  phone VARCHAR(40) DEFAULT NULL,
  zipcode VARCHAR(20) DEFAULT NULL,
  include_location TINYINT(1) DEFAULT 0,
  enable_email_comm TINYINT(1) DEFAULT 0,
  enable_phone_comm TINYINT(1) DEFAULT 0,
  is_admin TINYINT(1) DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prayer_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  is_anonymous TINYINT(1) DEFAULT 0,
  is_approved TINYINT(1) DEFAULT 0,
  is_answered TINYINT(1) DEFAULT 0,
  like_count INT NOT NULL DEFAULT 0,
  comments_count INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prayer_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  user_id INT NOT NULL,
  comment_text TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (request_id) REFERENCES prayer_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prayer_prays (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  user_id INT NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (request_id) REFERENCES prayer_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prayer_flags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  user_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (request_id) REFERENCES prayer_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS canned_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  text VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO canned_comments (text) VALUES
('Amen!'),
('God is near'),
('God is so good!'),
('God is working'),
('God will provide'),
('Here for you'),
('I\'ve been there too'),
('May God give you strength'),
('May you feel God\'s love'),
('Praise the Lord!'),
('Praying'),
('Praying for peace!'),
('Sending Love'),
('So happy to hear this!'),
('Thanks for being vulnerable'),
('Thank you for sharing'),
('What a blessing!');
