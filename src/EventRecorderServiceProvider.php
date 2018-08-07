<?php

namespace Denismitr\EventRecorder;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventRecorderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ( ! class_exists('CreateRecordedEventsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_recorded_events_table.php.stub' =>
                    database_path('migrations/' . date('Y_m_d_His', time()) . '_create_recorded_events_table.php'),
            ], 'migrations');
        }
    }

    public function register()
    {
        Event::subscribe(EventSubscriber::class);
    }
}