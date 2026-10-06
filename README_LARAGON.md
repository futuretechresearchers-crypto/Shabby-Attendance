Open:

   http://localhost/attendance_system/

Login:

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
