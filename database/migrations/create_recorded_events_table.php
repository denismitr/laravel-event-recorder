<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordedEventsTable extends Migration
{
    public function up()
    {
        $triggeredByType = config('event-recorder.triggered_by_id_type');
        $eventNameMaxLength = config('event-recorder.max_length.event_name');
        $eventDescriptionMaxLength = config('event-recorder.max_length.event_description');

        Schema::create('recorded_events', function (Blueprint $table) use (
            $triggeredByType,
            $eventNameMaxLength,
            $eventDescriptionMaxLength
        ) {
            $table->bigIncrements('id');
            $table->{$triggeredByType}('triggered_by_id')->nullable()->index();
            $table->string('name', $eventNameMaxLength);
            $table->string('class');
            $table->string('description', $eventDescriptionMaxLength);
            $table->json('properties');
            $table->json('triggered_by_properties')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recorded_events');
    }
}