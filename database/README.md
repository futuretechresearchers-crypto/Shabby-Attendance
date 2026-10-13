# Database setup and safety

Both applications use the existing `school_attendance` database. The revised application uses its current unprefixed tables (`users`, `students`, `attendance`); the classroom demo uses separate `unrevised_*` tables.

`database.sql` is a compatibility notice only. It contains no destructive operations. Do not run older copies of this file: the previous version began by dropping all three revised tables.

## Local unrevised setup

1. Back up your database using phpMyAdmin's Export.
2. Select the existing `school_attendance` database; do not create or drop a database.
3. Import `unrevised_setup.sql`. It creates only prefixed tables and adds seed rows only when missing.
4. The classroom demo login is `admin` / `admin123`; it is plaintext by design and must remain local.

The sample course and student identifiers here are reasonable local samples; the referenced 19-page PDF was not present in the supplied workspace, so exact starter seed values could not be verified.

## Revised schema inspection

Run `revised_migration.sql` first and save its duplicate/orphan results and `SHOW CREATE TABLE` output. The local inspection on 2026-10-13 found 1 user, 9 students, and 47 attendance rows; zero duplicate usernames, student numbers, or student/date pairs; unique student-number and student/date keys; and no username unique key or attendance foreign key. Two historical attendance rows (IDs 76 and 77, student IDs 14 and 16) refer to missing students. They were preserved. Do not delete or modify these records without an approved resolution. Consequently, no foreign key was added. Revised student deletion instead deletes only matching attendance rows and the student within one transaction.

The local DB was safely updated after duplicate checks: password width to VARCHAR(255), unique username index, and attendance status ENUM(Present, Absent, Late). Each change preserves existing values. The equivalent ALTER statements are included as commented guidance in `revised_migration.sql`; inspect current schema first because DDL is not safely repeatable without checking for an existing equivalent key/type. Existing student-number and student/date unique keys were retained.

The current Laragon project config points to `localhost`, user `root`, an empty password, and `school_attendance`. Keep deployment secrets outside version control and adapt local config for your machine.
