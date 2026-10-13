# COMSC 6 Student Attendance System

This repository contains two distinct PHP applications for the laboratory **Revising a Shabby Student Attendance System — From “It Works” to “It’s Good Software.”** The revised app is the existing dashboard-based application at the project root. The intentionally insecure original-style app is isolated under `unrevised/` for local instruction only.

## Requirements and Laragon setup

- Windows with Laragon, Apache, PHP 8.x and MySQL/MariaDB.
- Existing `school_attendance` database. Do not drop or recreate it.
- Project folder: `C:\laragon\www\attendance_system_revised`.

Start Apache and MySQL in Laragon. Configure `config/database.php` for the local database account if your Laragon credentials differ. The current source expects localhost / root / empty password. Review and back up the existing database before setup.

## Structure

```text
attendance_system_revised/
  unrevised/             original-style local demo (separate session and tables)
  revised/               stable revised login entry route
  attendance/            revised attendance form and processor
  students/              revised student CRUD
  reports/               revised attendance report
  includes/ config/ css/ shared revised support
  database/              isolated setup and schema inspection scripts
  documentation/         comparison, test plan, and evidence checklist
```

## Database setup and isolation

Both versions connect to the existing `school_attendance` database. The revised system uses `users`, `students`, and `attendance`. The unrevised demo uses `unrevised_users`, `unrevised_students`, and `unrevised_attendance`, so demo data and plaintext sample password do not enter revised tables.

`database.sql` is a harmless compatibility notice. For the demo, export a backup first, select the existing database in phpMyAdmin, then import `database/unrevised_setup.sql`. It only creates prefixed tables and adds missing sample rows. For the revised schema, first run `database/revised_migration.sql` and review its duplicate checks and `SHOW CREATE TABLE` output. It intentionally performs no ALTER operations because the live schema was not available for inspection. Resolve any duplicates explicitly before adding a unique key; never delete records just to make a migration pass.

The demo credential is `admin` / `admin123`; it is stored in plaintext only in the local `unrevised_users` seed table. Never deploy `unrevised/` publicly.

## Start and switch versions

Open [http://localhost/attendance_system_revised/](http://localhost/attendance_system_revised/). It enters the original-style login by default. Switch to revised at `/login.php` or `/revised/login.php`; each login page links to the other version. Use the revised account provisioned in the existing `users` table; this README does not publish its credentials.

The original and revised applications use distinct PHP session names. Their pages and tables are separate. Revised features include dashboard summaries/trends, student search/edit/pagination, filtered paginated reports, validated attendance, prepared statements, password verification, and reusable navigation.

## Laboratory comparison and evidence

See `documentation/ANALYSIS_COMPARISON_LAB.txt` for source-based analysis and `documentation/TEST_RESULTS.txt` for the manual test matrix. Take real screenshots and save them under `documentation/evidence/` following the named checklist there. The referenced PDF was not included with the supplied prompt, so the exact PDF seed values and page-specific original behavior remain to be reconciled.

## Troubleshooting

- Database unavailable: start MySQL in Laragon and check `config/database.php` host/account/database.
- Missing tables: import only the relevant reviewed script into the existing database.
- Revised login rejected: inspect the existing `users` table; do not overwrite credentials by importing the former `database.sql`.
- Broken paths after renaming the folder: revised links derive the project folder name; the unrevised `UNREVISED_BASE` constant is currently specific to this project folder.
- Blank/fatal page: inspect the PHP/Apache error log; raw database errors should not be shown to regular users.

## Safety note

The unrevised app intentionally demonstrates plaintext authentication, SQL concatenation, weak validation, unescaped output and GET deletion. Use it only on an isolated local Laragon installation with sample data. Do not deploy it on a public or shared server.
