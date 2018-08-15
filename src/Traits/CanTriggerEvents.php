<?php

namespace Denismitr\EventRecorder\Traits;


trait CanTriggerEvents
{
    public function getEventAttributeBlacklist(): array
    {
        if (
            ! property_exists($this, 'eventAttributeBlacklist') ||
            ! is_array($this->eventAttributeBlacklist)
        ) {
            return [];
        }

        return $this->eventAttributeBlacklist;
    }

    public function getTriggeredByProperties(): array
    {
        if (property_exists($this, 'triggeredByProperties') && is_array($this->triggeredByProperties)) {
            return $this->triggeredByProperties;
        }

        $properties = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (! in_array($key, $this->getEventAttributeBlacklist()) && ! in_array($key, $this->getHidden())) {
                $properties[$key] = $this->attributes[$key];
            }
        }

        return $properties;
    }
}