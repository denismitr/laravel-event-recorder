<?php

namespace Denismitr\EventRecorder\Tests;


use Denismitr\EventRecorder\Models\RecordedEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\ConditionallyRecordableEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\EmptyEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\LongDescriptionEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet;
use Denismitr\EventRecorder\Tests\Stubs\Events\ShouldNotBeRecordedEvent;
use Denismitr\EventRecorder\Tests\Stubs\Events\UserTriggeredEvent;
use Denismitr\EventRecorder\Tests\Stubs\Models\UnpersistableUser;
use Denismitr\EventRecorder\Tests\Stubs\Models\User;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;
use Denismitr\JsonAttributes\JsonAttributes;

class EventSubscriberTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Denis',
            'email' => 'denis@test.com',
            'password' => 'secret',
            'secret' => 5,
        ]);
        $this->userNotToRecord = User::create(['name' => 'Admin', 'email' => 'secret@test.com']);
        $this->unpersistableUser = UnpersistableUser::create(['name' => 'Admin', 'email' => 'secret@test.com']);
        $this->wallet = Wallet::create(['amount' => 0, 'user_id' => $this->user->id]);
    }

    /** @test */
    public function it_will_not_record_events_without_the_ShouldBeRecored_interface()
    {
        event(new ShouldNotBeRecordedEvent());
        $this->assertCount(0, RecordedEvent::all());
    }

    /** @test */
    public function it_will_record_events_that_implement_ShouldBeStored()
    {
        event(new MoneyAddedToWallet($this->wallet, 1234));

        $this->assertCount(1, RecordedEvent::all());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(MoneyAddedToWallet::class, $recordedEvent->class);
        $this->assertEquals(1234, $recordedEvent->properties->get('amount'));
        $this->assertEquals($this->wallet->id, $recordedEvent->properties->get('wallet_id'));
        $this->assertEquals($this->user->id, $recordedEvent->properties->get('user_id'));
        $this->assertEquals('credit', $recordedEvent->properties->get('operation'));

        $this->assertDatabaseHas('recorded_events', [
            'name' => 'money_added_to_wallet',
            'class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet',
            'description' => "User with ID {$this->user->id} added 1234 to the wallet with ID {$this->wallet->id}"
        ]);
    }

    /** @test */
    public function it_will_record_conditionally_recordable_events_that_should_not_be_skipped()
    {
        $this->be($this->user);

        event(new ConditionallyRecordableEvent($this->wallet, 1234, $byAdmin = false));

        $this->assertCount(1, RecordedEvent::all());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(ConditionallyRecordableEvent::class, $recordedEvent->class);
        $this->assertEquals(1234, $recordedEvent->properties->get('amount'));
        $this->assertEquals($this->wallet->id, $recordedEvent->properties->get('wallet_id'));
        $this->assertEquals($this->user->id, $recordedEvent->properties->get('user_id'));
        $this->assertEquals('credit', $recordedEvent->properties->get('operation'));

        $this->assertDatabaseHas('recorded_events', [
            'name' => 'conditionally_recordable_event',
            'class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\ConditionallyRecordableEvent',
            'description' => "User with ID {$this->user->id} added $1234 to the wallet with ID {$this->wallet->id}"
        ]);
    }

    /** @test */
    public function it_will_not_record_conditionally_recordable_events_that_should_be_skipped()
    {
        $this->be($this->userNotToRecord);

        event(new ConditionallyRecordableEvent($this->wallet, 1234, $byAdmin = true));

        $this->assertCount(0, RecordedEvent::all());

        $this->assertDatabaseMissing('recorded_events', [
            'name' => 'conditionally_recordable_event',
            'class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\ConditionallyRecordableEvent',
        ]);
    }
    
    /** @test */
    public function it_can_handle_empty_array_and_null_description_implementation()
    {
        event(new EmptyEvent());

        $this->assertCount(1, RecordedEvent::all());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(EmptyEvent::class, $recordedEvent->class);
        $this->assertEquals('empty_event', $recordedEvent->name);
        $this->assertEquals('', $recordedEvent->description);
        $this->assertInstanceOf(JsonAttributes::class, $recordedEvent->properties);
        $this->assertEquals(0, $recordedEvent->properties->count());
        $this->assertSame([], $recordedEvent->properties->all());
    }

    /** @test */
    public function it_truncates_description_to_max_length_of_512()
    {
        event(new LongDescriptionEvent());

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(str_repeat('*', 512), $recordedEvent->description);
    }

    /** @test */
    public function it_stores_triggered_by_data_when_event_uses_triggered_by_user_trait()
    {
        $this->be($this->user);

        event(new UserTriggeredEvent($this->wallet, 1234));

        $recordedEvent = RecordedEvent::first();

        $this->assertEquals(UserTriggeredEvent::class, $recordedEvent->class);
        $this->assertEquals(1234, $recordedEvent->properties->get('amount'));
        $this->assertEquals($this->wallet->id, $recordedEvent->properties->get('wallet_id'));
        $this->assertEquals($this->user->id, $recordedEvent->properties->get('user_id'));
        $this->assertEquals('debit', $recordedEvent->properties->get('operation'));
        // User information should be stored
        $this->assertEquals($this->user->id, $recordedEvent->triggered_by_id);
        $this->assertEquals($this->user->id, $recordedEvent->triggered_by_properties->get('id'));
        $this->assertEquals($this->user->name, $recordedEvent->triggered_by_properties->get('name'));
        $this->assertEquals($this->user->email, $recordedEvent->triggered_by_properties->get('email'));
        // attibutes specified as hidden or
        $this->assertNull($recordedEvent->triggered_by_properties->get('password'));
        $this->assertNull($recordedEvent->triggered_by_properties->get('secret'));

        $this->assertDatabaseHas('recorded_events', [
            'name' => 'user_triggered_event',
            'triggered_by_id' => $this->user->id,
            'class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\UserTriggeredEvent',
            'description' => "User with ID {$this->user->id} subtracted amount of $1234 from the wallet with ID {$this->wallet->id}"
        ]);
    }

    /** @test */
    public function it_will_not_persist_the_attributes_of_a_user_who_does_not_use_CanTriggerEvents()
    {
        config()->set('event-recorder.triggered_by_class', UnpersistableUser::class);

        $this->be($this->unpersistableUser);

        event(new UserTriggeredEvent($this->wallet, 1234));

        $recordedEvent = RecordedEvent::first();

        $this->assertEmpty($recordedEvent->triggered_by_properties->all());
        $this->assertNotEmpty($recordedEvent->properties->all());
        $this->assertEquals($this->unpersistableUser->id, $recordedEvent->triggered_by_id);
    }
}