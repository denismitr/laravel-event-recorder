<?php

namespace Denismitr\EventRecorder\Tests;


use Denismitr\EventRecorder\Models\RecordedEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\EmptyEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\LongDescriptionEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet;
use Denismitr\EventRecorder\Tests\Stubs\Events\ShouldNotBeRecordedEvent;
use Denismitr\EventRecorder\Tests\Stubs\Models\User;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;
use Denismitr\JsonAttributes\JsonAttributes;

class EventSubscriberTest extends TestCase
{
    /**
     * @var Wallet
     */
    protected $wallet;

    /**
     * @var User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = User::create(['name' => 'Denis', 'email' => 'denis@test.com']);
        $this->wallet = Wallet::create(['amount' => 0, 'user_id' => $this->user->id]);
    }

    /** @test */
    public function it_will_not_record_events_without_the_ShouldBeRecored_interface()
    {
        event(new ShouldNotBeRecordedEvent());
        $this->assertCount(0, RecordedEvent::all());
    }

    /** @test */
    public function it_will_report_events_that_implement_ShouldBeStored()
    {
        event(new MoneyAddedToWallet($this->wallet, 1234));

        $this->assertCount(1, RecordedEvent::all());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(MoneyAddedToWallet::class, $recordedEvent->event_class);
        $this->assertEquals(1234, $recordedEvent->event_properties->get('amount'));
        $this->assertEquals($this->wallet->id, $recordedEvent->event_properties->get('wallet_id'));
        $this->assertEquals($this->user->id, $recordedEvent->event_properties->get('user_id'));
        $this->assertEquals('credit', $recordedEvent->event_properties->get('operation'));

        $this->assertDatabaseHas('recorded_events', [
            'event_name' => 'money_added_to_wallet',
            'event_class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet',
            'event_description' => "User with ID {$this->user->id} added 1234 to the wallet with ID {$this->wallet->id}"
        ]);
    }
    
    /** @test */
    public function it_can_handle_empty_array_and_null_description_implementation()
    {
        event(new EmptyEvent());

        $this->assertCount(1, RecordedEvent::all());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(EmptyEvent::class, $recordedEvent->event_class);
        $this->assertEquals('empty_event', $recordedEvent->event_name);
        $this->assertEquals('', $recordedEvent->event_description);
        $this->assertInstanceOf(JsonAttributes::class, $recordedEvent->event_properties);
        $this->assertEquals(0, $recordedEvent->event_properties->count());
        $this->assertSame([], $recordedEvent->event_properties->all());
    }

    /** @test */
    public function it_truncates_description_to_max_length_of_512()
    {
        event(new LongDescriptionEvent());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(str_repeat('*', 512), $recordedEvent->event_description);
    }
}