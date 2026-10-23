<?php
class PayrollCalculator {
    public static function buildItem($employee, $attendance, $additions = 0, $cashAdvance = 0, $storeDebt = 0, $otherDeductions = 0) {
        $daysWorked = 0;
        $overtimeHours = 0;
        $overtimePay = 0;
        $undertime = 0;
        $overtimeRate = (float) $employee['overtime_rate'];
        $undertimeRate = isset($employee['undertime_rate']) ? (float) $employee['undertime_rate'] : null;

        foreach ($attendance as $record) {
            if ($record['status'] === 'present') {
                $daysWorked += 1;
            }
            if ((float) $record['overtime_hours'] > 0 && (int) $record['overtime_approved'] === 1) {
                $overtimeHours += (float) $record['overtime_hours'];
                $recordRate = $overtimeRate;
                if ($record['overtime_type'] === 'rest_day' || $record['overtime_type'] === 'holiday') {
                    $recordRate *= 1.30;
                }
                $overtimePay += (float) $record['overtime_hours'] * $recordRate;
            }
            if (isset($record['undertime_hours']) && $undertimeRate !== null) {
                $undertime += (float) $record['undertime_hours'] * $undertimeRate;
            } else {
                $undertime += (float) $record['undertime_deduction'];
            }
        }

        $dailyRate = (float) $employee['daily_rate'];
        $basicPay = $daysWorked * $dailyRate;
        $grossPay = $basicPay + $overtimePay + (float) $additions;
        $totalDeductions = (float) $cashAdvance + (float) $storeDebt + $undertime + (float) $otherDeductions;

        return array(
            'days_worked' => $daysWorked,
            'daily_rate' => $dailyRate,
            'overtime_hours' => $overtimeHours,
            'overtime_rate' => $overtimeRate,
            'basic_pay' => $basicPay,
            'overtime_pay' => $overtimePay,
            'additions' => (float) $additions,
            'cash_advance' => (float) $cashAdvance,
            'store_debt' => (float) $storeDebt,
            'undertime' => $undertime,
            'other_deductions' => (float) $otherDeductions,
            'gross_pay' => $grossPay,
            'total_deductions' => $totalDeductions,
            'net_pay' => $grossPay - $totalDeductions
        );
    }
}
