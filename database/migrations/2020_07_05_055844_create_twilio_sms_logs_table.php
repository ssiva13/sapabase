<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->string('sid')->unique();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->string('body');
            $table->string('price');
            $table->string('price_unit');
            $table->string('to');
            $table->string('from');
            $table->string('direction');
            $table->string('date_sent');
            $table->string('status');
            $table->timestamps();


            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_sms_logs');
    }
}
