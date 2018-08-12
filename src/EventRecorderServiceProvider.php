<?php

namespace Denismitr\EventRecorder;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventRecorderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/event-recorder.php' => config_path('event-recorder.php'),
        ], 'config');

        if ( ! class_exists('CreateRecordedEventsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_recorded_events_table.php' =>
                    database_path('migrations/' . date('Y_m_d_His', time()) . '_create_recorded_events_table.php'),
            ], 'migrations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/event-recorder.php',
            'event-recorder'
        );

        Event::subscribe(EventSubscriber::class);
    }
}