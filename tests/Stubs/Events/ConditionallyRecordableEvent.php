<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Events;


use Denismitr\EventRecorder\ConditionallyRecordable;
use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;
use Denismitr\EventRecorder\Traits\TriggeredByUser;

class ConditionallyRecordableEvent extends ConditionallyRecordable
{
    use TriggeredByUser;

    /**
     * @var Wallet
     */
    public $wallet;

    /**
     * @var int
     */
    public $amount;

    /**
     * @var bool
     */
    public $byAdmin;

    /**
     * MoneyAddedToWallet constructor.
     * @param Wallet $wallet
     * @param int $amount
     * @param bool $byAdmin
     */
    public function __construct(Wallet $wallet, int $amount, $byAdmin = false)
    {
        $this->wallet = $wallet;
        $this->amount = $amount;
        $this->byAdmin = $byAdmin;
    }

    public function getProperties(): array
    {
        return [
            'wallet_id' => $this->wallet->id,
            'amount' => $this->amount,
            'user_id' => $this->getTriggeredById(),
            'operation' => 'credit',
        ];
    }

    public function getDescription(): string
    {
        return vsprintf("User with ID %s added $%d to the wallet with ID %s", [
            $this->getTriggeredById(),
            $this->amount,
            $this->wallet->id,
        ]);
    }

    public function shouldBeSkipped(): bool
    {
        return !! $this->byAdmin;
    }
}