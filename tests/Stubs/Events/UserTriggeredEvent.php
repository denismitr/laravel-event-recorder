<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Events;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;
use Denismitr\EventRecorder\TriggeredByUser;

class UserTriggeredEvent implements ShouldBeRecorded
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
            'user_id' => $this->getTriggeredById(),
            'operation' => 'debit',
        ];
    }

    public function getDescription(): string
    {
        return vsprintf("User with ID %s subtracted amount of $%d from the wallet with ID %s", [
            $this->getTriggeredById(),
            $this->amount,
            $this->wallet->id,
        ]);
    }
}