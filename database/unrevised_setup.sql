-- Safe, isolated local demonstration tables in the existing school_attendance DB.
-- No DROP statements. Run while connected to school_attendance.
CREATE TABLE IF NOT EXISTS unrevised_users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS unrevised_students (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(30) NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  course VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS unrevised_attendance (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  status VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO unrevised_users (username, password)
SELECT 'admin', 'admin123' WHERE NOT EXISTS
 (SELECT 1 FROM unrevised_users WHERE username = 'admin');

INSERT INTO unrevised_students (student_number, first_name, last_name, course)
SELECT seed.student_number, seed.first_name, seed.last_name, seed.course
FROM (
 SELECT '2026-001' student_number, 'Juan' first_name, 'Dela Cruz' last_name, 'BS Information Technology' course
 UNION ALL SELECT '2026-002', 'Maria', 'Santos', 'BS Information Technology'
 UNION ALL SELECT '2026-003', 'Pedro', 'Garcia', 'BS Computer Science'
 UNION ALL SELECT '2026-004', 'Ana', 'Reyes', 'BS Information Technology'
 UNION ALL SELECT '2026-005', 'Mark', 'Torres', 'BS Computer Science'
) seed
WHERE NOT EXISTS (SELECT 1 FROM unrevised_students s WHERE s.student_number = seed.student_number);
