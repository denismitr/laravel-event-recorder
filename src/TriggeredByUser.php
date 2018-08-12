<?php

namespace Denismitr\EventRecorder;


trait TriggeredByUser
{
    public function getTriggeredById()
    {
        return auth()->check() ? auth()->user()->id : null;
    }
}