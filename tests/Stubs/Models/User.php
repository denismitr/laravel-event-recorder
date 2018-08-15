<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Models;


use Denismitr\EventRecorder\Traits\CanTriggerEvents;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable, CanTriggerEvents;

    protected $guarded = [''];

    public function isAdmin(): bool
    {
        return !! $this->admin;
    }


}