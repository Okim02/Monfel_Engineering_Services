-- Allow direct employee deletion while preserving linked login accounts.
ALTER TABLE users
    DROP FOREIGN KEY users_ibfk_1,
    ADD CONSTRAINT fk_users_employee_set_null
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL;

ALTER TABLE attendance
    DROP FOREIGN KEY attendance_ibfk_1,
    ADD CONSTRAINT fk_attendance_employee_cascade
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE cash_advances
    DROP FOREIGN KEY cash_advances_ibfk_1,
    ADD CONSTRAINT fk_cash_advances_employee_cascade
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE store_debts
    DROP FOREIGN KEY store_debts_ibfk_1,
    ADD CONSTRAINT fk_store_debts_employee_cascade
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE employee_project_assignments
    DROP FOREIGN KEY employee_project_assignments_ibfk_1,
    ADD CONSTRAINT fk_assignments_employee_cascade
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE payroll_items
    DROP FOREIGN KEY payroll_items_ibfk_2,
    ADD CONSTRAINT fk_payroll_items_employee_cascade
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;
