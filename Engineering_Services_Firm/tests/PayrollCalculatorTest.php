<?php
require_once dirname(__DIR__) . '/Backend/services/PayrollCalculator.php';

function assertSameValue($expected, $actual, $message) {
    if (abs($expected - $actual) > 0.0001) {
        throw new RuntimeException($message . ': expected ' . $expected . ', got ' . $actual);
    }
}

$employee = array('daily_rate' => 500, 'overtime_rate' => 75);
$attendance = array(
    array('status' => 'present', 'overtime_hours' => 2, 'overtime_type' => 'regular', 'overtime_approved' => 1, 'undertime_deduction' => 0),
    array('status' => 'present', 'overtime_hours' => 1, 'overtime_type' => 'rest_day', 'overtime_approved' => 1, 'undertime_deduction' => 50),
    array('status' => 'present', 'overtime_hours' => 4, 'overtime_type' => 'regular', 'overtime_approved' => 0, 'undertime_deduction' => 0)
);

$result = PayrollCalculator::buildItem($employee, $attendance, 100, 150, 50, 25);
assertSameValue(3, $result['days_worked'], 'days worked');
assertSameValue(3, $result['overtime_hours'], 'approved overtime hours');
assertSameValue(247.50, $result['overtime_pay'], 'overtime pay');
assertSameValue(1847.50, $result['gross_pay'], 'gross pay');
assertSameValue(275, $result['total_deductions'], 'total deductions');
assertSameValue(1572.50, $result['net_pay'], 'net pay');

$hourBasedEmployee = array('daily_rate' => 600, 'overtime_rate' => 85, 'undertime_rate' => 75);
$hourBasedAttendance = array(
    array('status' => 'present', 'overtime_hours' => 0, 'overtime_type' => 'regular', 'overtime_approved' => 0, 'undertime_hours' => 2)
);
$hourBasedResult = PayrollCalculator::buildItem($hourBasedEmployee, $hourBasedAttendance);
assertSameValue(150, $hourBasedResult['undertime'], 'proportional undertime deduction');

echo "PayrollCalculator tests passed.\n";
