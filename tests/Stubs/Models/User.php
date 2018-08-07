<?php

namespace Denismitr\EventRecorder\Tests\Stubs\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [''];

    public function isAdmin(): bool
    {
        return !! $this->admin;
    }
}