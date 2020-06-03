<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number')->nullable()->default(null);
            $table->string('inbound_recording', 60)->default('do-not-record');
            $table->string('outbound_recording', 60)->default('do-not-record');
            $table->timestamps();
        });

        // Create Twilio number
        $newTwilioNumber = new \App\Models\TwilioNumber();
        $newTwilioNumber->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_numbers');
    }
}
