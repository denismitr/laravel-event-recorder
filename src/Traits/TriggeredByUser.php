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
        if (auth()->check()) {
            $user = auth()->user();

            if (method_exists($user, 'getTriggeredByProperties')) {
                return $user->getTriggeredByProperties();
            }
        }

        return null;
    }
}