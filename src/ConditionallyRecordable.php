<?php

namespace Denismitr\EventRecorder;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;

abstract class ConditionallyRecordable implements ShouldBeRecorded
{
    public function shouldBeSkipped(): bool
    {
        return false;
    }
}