## Laravel Event Recorder

## Author
Denis Mitrofanov<br>

### Requirements
PHP 7.1 or higher
MYSQL 5.7 or higher
or 
POSTGRES probably any 9.* version or higher will do

### Overview

Any class that should be recorded to DB must implement the `Denismitr\EventRecorder\Contracts\ShouldBeRecorded` 
interface. Which enforces the concrete implementation of two methods `getProperties(): array` 
and `getDescription(): ?string` methods. Properties are an array of important **key-value** pairs for the event. 
(see example below) and description is a human readable form of the event description. The later method can return null, 
if for some reason the description is not required. The properties are stored in **json** format and description is a 
**nullable** string field.

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

    public function getDescription(): ?string
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

$this->assertEquals(MoneyAddedToWallet::class, $recordedEvent->event_class);
// json event_properties
$this->assertEquals(1234, $recordedEvent->event_properties->get('amount'));
$this->assertEquals($this->wallet->id, $recordedEvent->event_properties->get('wallet_id'));
$this->assertEquals($this->user->id, $recordedEvent->event_properties->get('user_id'));
$this->assertEquals('credit', $recordedEvent->event_properties->get('operation'));

$this->assertDatabaseHas('recorded_events', [
    'event_name' => 'money_added_to_wallet',
    'event_class' => 'Denismitr\EventRecorder\Tests\Stubs\Events\MoneyAddedToWallet',
    'event_description' => "User with ID {$this->user->id} added 1234 to the wallet with ID {$this->wallet->id}"
]);
```

Two important things to notice: 
* first - this package uses a dependency [denismitr/laravel-json-attributes](https://github.com/denismitr/laravel-json-attributes) 
to handle json properties in an elegant way. See the [docs](https://github.com/denismitr/laravel-json-attributes) for more information.
* second - there is a column **event_name** in `recorded_events` it is being generated from
the full event class in the form of **snake cased class name** (without the namespace).