<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_mesages', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->string('action_id');
            $table->integer('automation2_id')->unsigned();
            $table->text('type');
            $table->string('from');
            $table->string('from_name');
            $table->text('message');
            $table->text('subject');
            $table->text('reply_to');
            $table->timestamps();

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_mesages');
    }
}
