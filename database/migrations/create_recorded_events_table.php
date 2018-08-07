<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordedEventsTable extends Migration
{
    public function up()
    {
        Schema::create('recorded_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_class');
            $table->string('event_description', 512)->nullable();
            $table->json('event_properties');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recorded_events');
    }
}