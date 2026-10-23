<?php
class EventDispatcher {
    private $listeners = array();

    public function listen($eventName, callable $listener) {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = array();
        }
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch($eventName, array $payload = array()) {
        if (empty($this->listeners[$eventName])) {
            return;
        }
        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func($listener, $payload);
        }
    }
}
