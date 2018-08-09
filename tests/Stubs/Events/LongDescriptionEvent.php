<?php
/**
 * Created by PhpStorm.
 * User: denismitr
 * Date: 09.08.2018
 * Time: 21:47
 */

namespace Denismitr\EventRecorder\Tests\Stubs\Events;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;

class LongDescriptionEvent implements ShouldBeRecorded
{
    public function getProperties(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return str_repeat('*', 1000);
    }

}