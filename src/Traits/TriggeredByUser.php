<?php

namespace Denismitr\EventRecorder\Traits;


trait TriggeredByUser
{
    public function getTriggeredById()
    {
        return auth()->check() ? auth()->user()->id : null;
    }

    public function getTriggeredBy()
    {
        if ( ! auth()->check()) {
            return null;
        }

        $user = auth()->user();

        return $user->toArray();
    }
}