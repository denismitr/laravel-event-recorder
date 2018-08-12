<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Models;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    protected $guarded = [''];

    public function isAdmin(): bool
    {
        return !! $this->admin;
    }


}