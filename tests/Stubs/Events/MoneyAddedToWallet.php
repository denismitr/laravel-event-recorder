<?php
/**
 * Created by PhpStorm.
 * User: denismitr
 * Date: 07.08.2018
 * Time: 20:20
 */

namespace Denismitr\EventRecorder\Tests\Stubs\Events;


use Denismitr\EventRecorder\Contracts\ShouldBeRecorded;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;

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