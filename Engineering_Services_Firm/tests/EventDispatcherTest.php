<?php
require_once dirname(__DIR__) . '/Backend/events/EventDispatcher.php';

$dispatcher = new EventDispatcher();
$received = array();
$dispatcher->listen('attendance.recorded', function ($payload) use (&$received) {
    $received[] = $payload['attendance_id'];
});
$dispatcher->listen('attendance.recorded', function ($payload) use (&$received) {
    $received[] = $payload['employee_id'];
});
$dispatcher->dispatch('attendance.recorded', array('attendance_id' => 14, 'employee_id' => 7));

if ($received !== array(14, 7)) {
    throw new RuntimeException('Event listeners did not receive the expected payload.');
}

echo "EventDispatcher tests passed.\n";
