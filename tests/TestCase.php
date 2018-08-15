<?php

namespace Denismitr\EventRecorder\Tests;

use Denismitr\EventRecorder\EventRecorderServiceProvider;
use Denismitr\EventRecorder\Tests\Stubs\Models\User;
use Denismitr\EventRecorder\Tests\Stubs\Models\Wallet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @var Wallet
     */
    protected $wallet;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $unpersistableUser;

    /**
     * @var User
     */
    protected $userNotToRecord;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            EventRecorderServiceProvider::class
        ];
    }

    protected function getEnvironmentSetup($app)
    {
        if (getenv('USE_MYSQL') !== 'yes') {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }
    }

    protected function setUpDatabase($app)
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password')->default('secret');
            $table->tinyInteger('secret')->default(1);
            $table->boolean('admin')->default(false);
            $table->timestamps();
        });

        Schema::create('unpersistable_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->integer('amount')->default(0);
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/create_recorded_events_table.php';

        (new \CreateRecordedEventsTable())->up();
    }
}