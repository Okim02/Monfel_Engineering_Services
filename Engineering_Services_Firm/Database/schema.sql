CREATE DATABASE IF NOT EXISTS engineering_services_firm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE engineering_services_firm;

CREATE TABLE positions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE salary_grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    daily_rate DECIMAL(12,2) NOT NULL,
    overtime_rate DECIMAL(12,2) NOT NULL,
    undertime_rate DECIMAL(12,2) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    position_id INT UNSIGNED NULL,
    salary_grade_id INT UNSIGNED NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (salary_grade_id) REFERENCES salary_grades(id),
    INDEX idx_employees_name (full_name)
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner', 'timekeeper', 'worker') NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE attendance_authorizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    authorized_by INT UNSIGNED NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    authorized_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    UNIQUE KEY uq_attendance_authorization_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (authorized_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(255) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_projects_name (name)
) ENGINE=InnoDB;

CREATE TABLE employee_project_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    assigned_from DATE NOT NULL,
    assigned_to DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    INDEX idx_assignments_employee_project (employee_id, project_id),
    INDEX idx_assignments_dates (assigned_from, assigned_to)
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    time_in TIME NULL,
    time_out TIME NULL,
    status ENUM('present', 'absent', 'leave') NOT NULL DEFAULT 'present',
    leave_notified_at DATETIME NULL,
    late_minutes DECIMAL(7,2) NOT NULL DEFAULT 0,
    late_deduction_hours DECIMAL(7,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(7,2) NOT NULL DEFAULT 0,
    overtime_type ENUM('regular', 'rest_day', 'holiday') NOT NULL DEFAULT 'regular',
    overtime_approved TINYINT(1) NOT NULL DEFAULT 0,
    overtime_approved_by INT UNSIGNED NULL,
    overtime_approved_at DATETIME NULL,
    undertime_hours DECIMAL(7,2) NOT NULL DEFAULT 0,
    undertime_deduction DECIMAL(12,2) NOT NULL DEFAULT 0,
    notes VARCHAR(500) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_attendance_employee_date_project (employee_id, work_date, project_id),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (overtime_approved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_attendance_date (work_date),
    INDEX idx_attendance_employee_date (employee_id, work_date),
    INDEX idx_attendance_project (project_id)
) ENGINE=InnoDB;

CREATE TABLE cash_advances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    advance_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    amount_deducted DECIMAL(12,2) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    status ENUM('open', 'settled', 'void') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_cash_advances_employee_status (employee_id, status)
) ENGINE=InnoDB;

CREATE TABLE store_debts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    debt_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    amount_deducted DECIMAL(12,2) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    status ENUM('open', 'settled', 'void') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_store_debts_employee_status (employee_id, status)
) ENGINE=InnoDB;

CREATE TABLE payroll_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    status ENUM('draft', 'finalized') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NOT NULL,
    finalized_by INT UNSIGNED NULL,
    finalized_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payroll_period (period_start, period_end),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (finalized_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE payroll_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_period_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    employee_code_snapshot VARCHAR(20) NOT NULL,
    employee_name_snapshot VARCHAR(150) NOT NULL,
    position_snapshot VARCHAR(80) NULL,
    days_worked DECIMAL(6,2) NOT NULL DEFAULT 0,
    daily_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(7,2) NOT NULL DEFAULT 0,
    overtime_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
    basic_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    overtime_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    additions DECIMAL(12,2) NOT NULL DEFAULT 0,
    cash_advance DECIMAL(12,2) NOT NULL DEFAULT 0,
    store_debt DECIMAL(12,2) NOT NULL DEFAULT 0,
    undertime DECIMAL(12,2) NOT NULL DEFAULT 0,
    other_deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
    net_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payroll_item_employee_period (payroll_period_id, employee_id),
    FOREIGN KEY (payroll_period_id) REFERENCES payroll_periods(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payroll_adjustments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_item_id INT UNSIGNED NOT NULL,
    adjustment_type ENUM('addition', 'deduction') NOT NULL,
    label VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payroll_item_id) REFERENCES payroll_items(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB;

CREATE TABLE settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` VARCHAR(255) NULL,
    description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`, description) VALUES
('work_start_time', '08:00:00', 'Confirmed normal work start time.'),
('work_end_time', '17:00:00', 'Confirmed normal work end time.'),
('morning_break_start', '10:00:00', 'Confirmed morning break start.'),
('morning_break_end', '10:30:00', 'Confirmed morning break end.'),
('lunch_break_start', '12:00:00', 'Confirmed lunch break start.'),
('lunch_break_end', '13:00:00', 'Confirmed lunch break end.'),
('afternoon_break_start', '', 'TODO: Confirm the afternoon break schedule.'),
('afternoon_break_end', '', 'TODO: Confirm the afternoon break duration; client response was 3:00 to 3.'),
('late_rounding_rule', '', 'TODO: Confirm the complete late deduction rounding rule.'),
('undertime_formula', 'hours_times_undertime_rate', 'Undertime deduction is proportional to recorded undertime hours.'),
('rest_holiday_overtime_multiplier', '1.30', 'Confirmed 30% premium for rest days and holidays.'),
('cash_advance_repayment_method', '', 'TODO: Confirm whether repayment is one-time or installment-based.'),
('store_debt_repayment_method', '', 'TODO: Confirm whether repayment is one-time or installment-based.');
