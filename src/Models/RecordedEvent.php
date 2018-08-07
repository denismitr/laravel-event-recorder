<?php

namespace Denismitr\EventRecorder\Models;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Illuminate\Database\Eloquent\Model;

class RecordedEvent extends Model
{
    public $guarded = ['id'];

    public static function recordEvent(ShouldBeRecorded $event): self
    {
        $recordedEvent = new static();
        $recordedEvent->event_class = get_class($event);
        $recordedEvent->event_properties = serialize($event->getProperties());
        $recordedEvent->event_description = $event->getDescription();
        $recordedEvent->save();

        return $recordedEvent;
    }
}