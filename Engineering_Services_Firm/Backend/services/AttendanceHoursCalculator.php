<?php
class AttendanceHoursCalculator {
    public static function calculate($timeIn, $timeOut) {
        $start = self::toMinutes($timeIn);
        $end = self::toMinutes($timeOut);

        if ($start === null || $end === null || $end < $start) {
            return array('overtime' => 0, 'undertime' => 0);
        }

        $scheduledWork = self::overlap($start, $end, 480, 720)
            + self::overlap($start, $end, 780, 1020);
        $overtimeMinutes = self::overlap($start, $end, 0, 480)
            + self::overlap($start, $end, 1020, 1440);

        return array(
            'overtime' => round($overtimeMinutes / 60, 2),
            'undertime' => round(max(0, 480 - $scheduledWork) / 60, 2)
        );
    }

    private static function toMinutes($time) {
        if (!$time || !preg_match('/^(\d{2}):(\d{2})/', $time, $matches)) {
            return null;
        }
        $hour = (int) $matches[1];
        $minute = (int) $matches[2];
        if ($hour > 23 || $minute > 59) {
            return null;
        }
        return ($hour * 60) + $minute;
    }

    private static function overlap($start, $end, $rangeStart, $rangeEnd) {
        return max(0, min($end, $rangeEnd) - max($start, $rangeStart));
    }
}
