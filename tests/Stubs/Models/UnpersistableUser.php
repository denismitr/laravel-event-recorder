<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Models;


use Denismitr\EventRecorder\Traits\CanTriggerEvents;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UnpersistableUser extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    protected $guarded = [''];

    public function isAdmin(): bool
    {
        return false;
    }


}