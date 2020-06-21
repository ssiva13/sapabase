<?php

use Acelle\Model\TwilioNumber;
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
            $table->uuid('uid');
            $table->string('number')->nullable()->default(null);
            $table->integer('user_id')->unsigned();
            $table->integer('admin_id')->unsigned()->nullable();
            $table->decimal('charges', 11,5)->nullable();
            $table->string('inbound_recording', 60)->default('do-not-record');
            $table->string('outbound_recording', 60)->default('do-not-record');
            $table->string('status')->nullable()->default('active');
            $table->timestamps();

            // foreign
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins');
        });

        // Create Twilio number
        $newTwilioNumber = new TwilioNumber();
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
