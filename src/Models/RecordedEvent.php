<?php

namespace Denismitr\EventRecorder\Models;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\EventRecorder\EventName;
use Denismitr\JsonAttributes\JsonAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecordedEvent extends Model
{
    public $guarded = ['id'];

    protected $casts = ['properties' => 'array', 'triggered_by_properties' => 'array'];

    /**
     * @return JsonAttributes
     */
    public function getPropertiesAttribute(): JsonAttributes
    {
        return JsonAttributes::create($this, 'properties');
    }

    /**
     * @return Builder
     */
    public function scopeWithProperties(): Builder
    {
        return JsonAttributes::scopeWithJsonAttributes('properties');
    }

    /**
     * @return JsonAttributes
     */
    public function getTriggeredByPropertiesAttribute(): JsonAttributes
    {
        return JsonAttributes::create($this, 'properties');
    }

    /**
     * @return Builder
     */
    public function scopeWithTriggeredByProperties(): Builder
    {
        return JsonAttributes::scopeWithJsonAttributes('properties');
    }

    public function triggeredBy()
    {
        return $this->belongsTo(config('event-recorder.triggered_by_class'), 'triggered_by_id');
    }

    /**
     * @param ShouldBeRecorded $event
     * @return RecordedEvent
     */
    public static function recordEvent(ShouldBeRecorded $event): self
    {
        $eventDescriptionMaxLength = config('event-recorder.max_length.event_description');

        $recordedEvent = new static();
        $recordedEvent->name = EventName::capture($eventClass = get_class($event));
        $recordedEvent->class = $eventClass;
        $recordedEvent->triggered_by_id = static::resolveTriggeredById($event);
        $recordedEvent->triggered_by_properties = static::resolveTriggeredBy($event);
        $recordedEvent->properties = $event->getProperties();
        $recordedEvent->description = str_limit($event->getDescription(), $eventDescriptionMaxLength, '');
        $recordedEvent->save();

        return $recordedEvent;
    }

    /**
     * @param $event
     * @return null|mixed
     */
    protected static function resolveTriggeredById($event)
    {
        if (method_exists($event, 'getTriggeredById')) {
            return $event->getTriggeredById();
        }

        return null;
    }

    protected static function resolveTriggeredBy($event)
    {
        if (method_exists($event, 'getTriggeredBy')) {
            return $event->getTriggeredBy();
        }

        return null;
    }
}