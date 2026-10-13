-- Safe, repeatable migration for the existing school_attendance schema.
-- Run preflight first. The procedure refuses duplicate or invalid data; it never
-- deletes/rewrites rows. It intentionally does not create an FK while orphans exist.
USE school_attendance;

SELECT username, COUNT(*) AS copies FROM users GROUP BY username HAVING COUNT(*) > 1;
SELECT student_number, COUNT(*) AS copies FROM students GROUP BY student_number HAVING COUNT(*) > 1;
SELECT student_id, attendance_date, COUNT(*) AS copies FROM attendance
 GROUP BY student_id, attendance_date HAVING COUNT(*) > 1;
SELECT a.id, a.student_id, a.attendance_date FROM attendance a
 LEFT JOIN students s ON s.id = a.student_id WHERE s.id IS NULL;
SHOW CREATE TABLE users;
SHOW CREATE TABLE students;
SHOW CREATE TABLE attendance;

DELIMITER //
DROP PROCEDURE IF EXISTS migrate_revised_attendance_schema//
CREATE PROCEDURE migrate_revised_attendance_schema()
BEGIN
  IF EXISTS (SELECT 1 FROM users GROUP BY username HAVING COUNT(*) > 1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Duplicate usernames: review records before migration';
  END IF;
  IF EXISTS (SELECT 1 FROM students GROUP BY student_number HAVING COUNT(*) > 1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Duplicate student numbers: review records before migration';
  END IF;
  IF EXISTS (SELECT 1 FROM attendance GROUP BY student_id, attendance_date HAVING COUNT(*) > 1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Duplicate attendance: review records before migration';
  END IF;
  IF EXISTS (SELECT 1 FROM attendance WHERE status NOT IN ('Present','Absent','Late')) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unsupported attendance statuses: review records before migration';
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
        AND INDEX_NAME = 'unique_username') THEN
    ALTER TABLE users ADD UNIQUE KEY unique_username (username);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
        AND COLUMN_NAME = 'password' AND COLUMN_TYPE = 'varchar(255)') THEN
    ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students'
        AND INDEX_NAME IN ('UNIQUE_student_number','unique_student_number')) THEN
    ALTER TABLE students ADD UNIQUE KEY unique_student_number (student_number);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance'
        AND INDEX_NAME = 'unique_student_date') THEN
    ALTER TABLE attendance ADD UNIQUE KEY unique_student_date (student_id, attendance_date);
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance'
        AND COLUMN_NAME = 'status'
        AND COLUMN_TYPE = "enum('Present','Absent','Late')") THEN
    ALTER TABLE attendance MODIFY status ENUM('Present','Absent','Late') NOT NULL;
  END IF;
END//
CALL migrate_revised_attendance_schema()//
DROP PROCEDURE migrate_revised_attendance_schema//
DELIMITER ;

-- The live database had two pre-existing orphan rows (IDs 76/77). Do not add
-- the attendance foreign key until a record-level resolution is explicitly approved.
-- Revised PHP deletion handles future related records transactionally meanwhile.
