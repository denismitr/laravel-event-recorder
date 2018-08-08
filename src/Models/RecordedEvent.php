<?php

namespace Denismitr\EventRecorder\Models;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\JsonAttributes\JsonAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecordedEvent extends Model
{
    public $guarded = ['id'];

    protected $casts = ['event_properties' => 'array'];

    /**
     * @return JsonAttributes
     */
    public function getEventPropertiesAttribute(): JsonAttributes
    {
        return JsonAttributes::create($this, 'event_properties');
    }

    /**
     * @return Builder
     */
    public function scopeWithEventProperties(): Builder
    {
        return JsonAttributes::scopeWithJsonAttributes('event_properties');
    }

    /**
     * @param ShouldBeRecorded $event
     * @return RecordedEvent
     */
    public static function recordEvent(ShouldBeRecorded $event): self
    {
        $recordedEvent = new static();
        $recordedEvent->event_class = get_class($event);
        $recordedEvent->event_properties = $event->getProperties();
        $recordedEvent->event_description = $event->getDescription();
        $recordedEvent->save();

        return $recordedEvent;
    }
}