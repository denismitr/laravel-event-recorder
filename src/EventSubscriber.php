<?php

namespace Denismitr\EventRecorder;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\EventRecorder\Models\RecordedEvent;

class EventSubscriber
{
    public function subscribe($events)
    {
        $events->listen('*', static::class . '@handleEvent');
    }

    /**
     * @param string $eventClass
     * @param array $payload
     */
    public function handleEvent(string $eventClass, array $payload): void
    {
        if ( ! $this->shouldBeRecorded($eventClass) || ! isset($payload[0]) ) {
            return;
        }

        $this->recordEvent($payload[0]);
    }

    /**
     * @param ShouldBeRecorded $event
     */
    public function recordEvent(ShouldBeRecorded $event): void
    {
        RecordedEvent::recordEvent($event);
    }

    /**
     * @param string $eventClass
     * @return bool
     */
    protected function shouldBeRecorded(string $eventClass): bool
    {
        if ( ! class_exists($eventClass) ) {
            return false;
        }

        return is_subclass_of($eventClass, ShouldBeRecorded::class);
    }
}