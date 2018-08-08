<?php
/**
 * Created by PhpStorm.
 * User: denismitr
 * Date: 08.08.2018
 * Time: 19:25
 */

namespace Denismitr\EventRecorder\Tests\Stubs\Events;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;

class EmptyEvent implements ShouldBeRecorded
{
    public function getProperties(): array
    {
        return [];
    }

    public function getDescription(): ?string
    {
        return null;
    }
}