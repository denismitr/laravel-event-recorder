<?php

namespace Denismitr\EventRecorder\Tests;


use Denismitr\EventRecorder\EventName;
use Denismitr\EventRecorder\Tests\Stubs\Events\EmptyEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet;

class EventNameTest extends TestCase
{
    /**
     * @test
     * @dataProvider sampleData
     */
    public function it_can_capture_event_name_from_full_class_namespace(string $in, string $out)
    {
        $this->assertEquals($out, EventName::capture($in));
    }

    public function sampleData(): array
    {
        return [
            [MoneyAddedToWallet::class, 'money_added_to_wallet'],
            [EmptyEvent::class, 'empty_event'],
            ['TestClass', 'test_class'],
            ['Fake\\TestClass', 'test_class'],
            [\Exception::class, 'exception'],
            [\stdClass::class, 'std_class'],
            ['\\TestClass', 'test_class'],
        ];
    }
}