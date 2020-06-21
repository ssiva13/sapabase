<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwilioFieldsInSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
//            $table->boolean('twilio_enabled')->default(false);
//            $table->string('twilio_account_sid')->nullable()->default(null);
//            $table->string('twilio_auth_token')->nullable()->default(null);
//            $table->string('twilio_application_sid')->nullable()->default(null);
//            $table->boolean('app_debug')->default(true);
//            $table->boolean('rtl')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
//            $table->dropColumn(['twilio_enabled', 'twilio_account_sid', 'twilio_auth_token', 'twilio_application_sid', 'app_debug', 'rtl']);
        });
    }
}
