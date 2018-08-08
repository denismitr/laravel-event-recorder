<?php
/**
 * Created by PhpStorm.
 * User: denismitr
 * Date: 08.08.2018
 * Time: 19:21
 */

namespace Denismitr\EventRecorder;


class EventName
{
    public static function capture(string $eventClass): string
    {
        $segments = explode('\\', $eventClass);

        return snake_case(last($segments));
    }
}