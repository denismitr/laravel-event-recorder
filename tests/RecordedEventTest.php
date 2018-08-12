<?php

namespace Denismitr\EventRecorder\Tests;


use Denismitr\EventRecorder\EventName;
use Denismitr\EventRecorder\Models\RecordedEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\EmptyEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet;
use Denismitr\EventRecorder\Tests\Stubs\Events\UserTriggeredEvent;
use Denismitr\EventRecorder\Tests\Stubs\Models\User;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;

class RecordedEventTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['name' => 'Denis', 'email' => 'denis@test.com']);
        $this->wallet = Wallet::create(['amount' => 0, 'user_id' => $this->user->id]);

        config()->set('event-recorder.triggered_by_class', User::class);
    }

    /** @test */
    public function it_has_relationship_with_user_who_triggered_event()
    {
        $this->be($this->user);

        event(new UserTriggeredEvent($this->wallet, 1234));

        $recordedEvent = RecordedEvent::with('triggeredBy')->first();

        $this->assertInstanceOf(User::class, $recordedEvent->triggeredBy);
        $this->assertTrue($this->user->is($recordedEvent->triggeredBy));
    }

    /** @test */
    public function it_will_return_null_if_user_that_triggered_the_event_was_deleted()
    {
        $this->be($this->user);

        event(new UserTriggeredEvent($this->wallet, 1234));

        $this->user->delete();
        // just to make sure
        $this->assertNull($this->user->fresh());

        $recordedEvent = RecordedEvent::with('triggeredBy')->first();
        $this->assertEquals(null, $recordedEvent->triggeredBy);
    }
}