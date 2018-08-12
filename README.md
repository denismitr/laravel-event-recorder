## Laravel Event Recorder
[![Build Status](https://travis-ci.org/denismitr/laravel-event-recorder.svg?branch=master)](https://travis-ci.org/denismitr/laravel-event-recorder)

## Author
Denis Mitrofanov<br>

### Requirements
PHP 7.0 or higher
MYSQL 5.7 or higher
or 
POSTGRES probably any 9.* version or higher will do

### Overview

Any class that should be recorded to DB must implement the `Denismitr\EventRecorder\Contracts\ShouldBeRecorded` 
interface. Which enforces the concrete implementation of two methods `getProperties(): array` 
and `getDescription(): string` methods. Properties are an array of important **key-value** pairs for the event. 
(see example below) and description is a human readable form of the event description. The properties are stored in **json** format and description is a 
**nullable** string field.

### Installation
Via composer (current version is 1.x)
`composer require denismitr/laravel-event-recorder`

```
'providers' => [
    // ...
    Denismitr\EventRecorder\EventRecorderServiceProvider::class,
];
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Denismitr\EventRecorder\EventRecorderServiceProvider" --tag="migrations"
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Denismitr\EventRecorder\EventRecorderServiceProvider" --tag="config"
```

After the migration has been published and you are happy with configuration, run the mirations:
`php artisan migrate`

### Usage
Let's imagine we have a `MoneyAddedToWallet` event:

```php
class MoneyAddedToWallet implements ShouldBeRecorded
{
    /**
     * @var Wallet
     */
    public $wallet;

    /**
     * @var int
     */
    public $amount;

    /**
     * MoneyAddedToWallet constructor.
     * @param Wallet $wallet
     * @param int $amount
     */
    public function __construct(Wallet $wallet, int $amount)
    {
        $this->wallet = $wallet;
        $this->amount = $amount;
    }

    public function getProperties(): array
    {
        return [
            'wallet_id' => $this->wallet->id,
            'amount' => $this->amount,
            'user_id' => $this->wallet->user_id,
            'operation' => 'credit',
        ];
    }

    public function getDescription(): string
    {
        return vsprintf("User with ID %s added %d to the wallet with ID %s", [
            $this->wallet->user_id,
            $this->amount,
            $this->wallet->id,
        ]);
    }
}
```

After it is fired. A record in the `recorded_events` table will be created. The following extract from test file 
hopefully explains what is going on.

```php
event(new MoneyAddedToWallet($this->wallet, 1234));

$recordedEvent = RecordedEvent::first();

$this->assertEquals(MoneyAddedToWallet::class, $recordedEvent->class);
// json properties
$this->assertEquals(1234, $recordedEvent->properties->get('amount'));
$this->assertEquals($this->wallet->id, $recordedEvent->properties->get('wallet_id'));
$this->assertEquals($this->user->id, $recordedEvent->properties->get('user_id'));
$this->assertEquals('credit', $recordedEvent->properties->get('operation'));

$this->assertDatabaseHas('recorded_events', [
    'name' => 'money_added_to_wallet',
    'class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet',
    'description' => "User with ID {$this->user->id} added 1234 to the wallet with ID {$this->wallet->id}"
]);
```

Two important things to notice: 
* first - this package uses a dependency [denismitr/laravel-json-attributes](https://github.com/denismitr/laravel-json-attributes) 
to handle json properties in an elegant way. See the [docs](https://github.com/denismitr/laravel-json-attributes) for more information.
* second - there is a column **name** in `recorded_events` it is being generated from
the full event class in the form of **snake cased class name** (without the namespace).

### Triggered By
As of version 1.0 package supports recording an ID of a user who triggered an event. 
If you use a trait `Denismitr\EventRecorder\TriggeredByUser` in the event class you wish
to record, when event occurres the user who is currently logged in ID will be saved along with 
other data for this event. The DB column in `recorded_events` used for this is called `triggered_by_id`.
There is a Laravel `belongsTo` relationship defined in `RecordedEvents` model. 
This way you can retrieve an instance of a user whose actions have triggered the event.

### Configuration
The contents of configuration file is as follows:

```php
return [
    'triggered_by_id_type' => 'unsignedInteger',

    'triggered_by_class' => 'App\User',

    'max_length' => [
        'event_name' => 100,
        'event_description' => 512,
    ]
];
``` 

If you need to change some of these properties, be sure to do that before you actually run 
the `php artisan migrate`, otherwise you will have to do either `php artisan migrate:fresh`
or if it is already impossible, you would have to manually create the logic for changing your DB schema.