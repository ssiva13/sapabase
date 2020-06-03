<?php

use Acelle\Model\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwilioClientNameInCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('twilio_client_name')->nullable()->default(null)->after('last_name');
        });

        $allUsers = Customer::select('id', 'first_name', 'last_name')->get();

        foreach ($allUsers as $allUser)
        {
            $name = trim(strtolower($allUser->first_name));

            if($allUser->last_name != '')
            {
                $name .= ' '. trim(strtolower($allUser->last_name));
            }

            $name = str_replace(' ', '_', $name);

            $checkIfNameAlreadyExists = Customer::where('twilio_client_name', $name)->count();

            if($checkIfNameAlreadyExists > 0)
            {
                $name = $name.'_'.$allUser->id;
            }

            $allUser->twilio_client_name = $name;
            $allUser->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['twilio_client_name']);
        });
    }
}
