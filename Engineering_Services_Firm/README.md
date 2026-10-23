# Engineering Services Firm

A PHP/MySQL system for managing engineering projects, employees, attendance, and payroll.

## Folder structure

- `Assets/` - CSS, JavaScript, and image files
- `Backend/` - controllers, models, payroll services, and configuration
- `Backend/events/` - event dispatcher and domain event infrastructure
- `Database/` - engineering firm schema, migrations, and seed data
- `Includes/` - shared PHP layout and authentication files
- `tests/` - focused tests for payroll calculations and authorization

## Core modules

- Employee and user management
- Project assignments
- Daily attendance tracking
- Overtime approval
- Payroll calculation and payslips
- Cash advances, store debts, and audit logs

## Initial setup

1. Start Apache and MySQL in XAMPP.
2. Import `Database/schema.sql` into MySQL.
3. Import `Database/seed.sql` for the confirmed position list.
4. Review `Backend/config/database.php` and update credentials if needed.
5. Open `http://localhost/Engineering_Services_Firm/`.

Unconfirmed client rules are stored in the `settings` table and are not silently guessed.

## Event-driven workflow

The shared dispatcher in `Backend/events/EventDispatcher.php` supports named domain events. Authentication currently emits `auth.login` and `auth.logout`; future modules can emit events such as `attendance.recorded`, `overtime.approved`, and `payroll.finalized` for audit logging and notifications without coupling those concerns to controllers.
