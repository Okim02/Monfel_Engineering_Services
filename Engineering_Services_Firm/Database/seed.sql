USE engineering_services_firm;

INSERT INTO positions (name) VALUES
('Timekeeper'),
('Toolkeeper'),
('Expediter'),
('Foreman'),
('Leadman'),
('Masonry'),
('Welder'),
('Skilled'),
('Labor');

INSERT INTO salary_grades (code, name, daily_rate, overtime_rate, undertime_rate) VALUES
('SG-470', 'Salary Grade 470', 470.00, 65.00, 58.75),
('SG-500', 'Salary Grade 500', 500.00, 70.00, 62.50),
('SG-550', 'Salary Grade 550', 550.00, 77.00, 68.75),
('SG-600', 'Salary Grade 600', 600.00, 85.00, 75.00),
('SG-650', 'Salary Grade 650', 650.00, 91.00, 81.25),
('SG-700', 'Salary Grade 700', 700.00, 98.00, 87.50),
('SG-750', 'Salary Grade 750', 750.00, 105.00, 93.75),
('SG-800', 'Salary Grade 800', 800.00, 115.00, 100.00),
('SG-850', 'Salary Grade 850', 850.00, 120.00, 106.25),
('SG-900', 'Salary Grade 900', 900.00, 130.00, 112.50);
