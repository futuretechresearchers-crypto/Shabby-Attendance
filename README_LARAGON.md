# Revised Student Attendance System — VS Code + Laragon

This is the revised/final version of the COMSC 6 laboratory activity.

## Important

The original laboratory procedure asks students to test the shabby version before revising it. This ZIP is intentionally the revised version so you can compare the two implementations directly.

## Installation in Laragon

1. Extract this folder into:

   C:\laragon\www\

2. Rename the extracted folder to:

   attendance_system

3. Start Apache and MySQL in Laragon.

4. Import `database.sql` into MariaDB/MySQL.

5. Open:

   http://localhost/attendance_system/

6. Login:

   Username: admin
   Password: admin123

## Main differences from shabby_attendance

- Password is hashed instead of stored as plaintext.
- Login uses prepared statements and password_verify().
- Session authentication is centralized in includes/auth.php.
- Database configuration is separated into config/database.php.
- Student CRUD is organized under students/.
- Attendance is organized under attendance/.
- Reports are organized under reports/.
- Header/footer are reusable.
- Student input is validated server-side.
- SQL queries use prepared statements for user-controlled values.
- Student numbers and usernames are unique.
- Attendance has a unique student/date constraint.
- Attendance status is restricted to Present, Absent, or Late.
- Duplicate attendance submissions update the existing record rather than creating duplicates.
- Database errors are logged rather than exposed to normal users.
- Student search and pagination are included.
- Attendance report uses an explicit INNER JOIN.
- Tables use only the columns required by the page.
- Delete requires browser confirmation.
- Logout clears the session and session cookie.
- Responsive layout and accessible labels/buttons are included.

## Note about the seed password

The `database.sql` file contains a bcrypt password hash for the laboratory password `admin123`, so the final application can use password_verify() without storing the plaintext password.
