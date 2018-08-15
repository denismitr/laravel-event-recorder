<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Models;


use Denismitr\EventRecorder\Traits\CanTriggerEvents;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable, CanTriggerEvents;

    protected $guarded = [''];

    // should not be stored in triggered_by_properties
    protected $hidden = ['password'];

    // should not be stored in triggered_by_properties
    protected $eventAttributeBlacklist = ['secret'];

    public function isAdmin(): bool
    {
        return !! $this->admin;
    }


}