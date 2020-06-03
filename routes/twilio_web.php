<?php

// Admin Panel After Login
Route::group(['middleware' => ['auth.admin', 'web', 'admin.permission.check'],'namespace' => 'Admin', 'prefix' => 'admin'], function () {

    Route::group(['prefix' => 'settings', 'middleware' => ['auth.admin.check']], function () {
        Route::post('calls/save-twilio-number', ['as' => 'admin.settings.calls.save-twilio-number', 'uses' => 'CallSettingController@saveTwilioNumber']);
        Route::resource('calls', 'CallSettingController', ['as' => 'admin.settings', 'only' => ['index','store']]);
    });
});

Route::post('/twilio/inbound-webhook/{number}', ['as' => 'front.twilio.inbound-webhook', 'uses' => 'Front\TwilioCallController@inboundWebhookHandler']);
Route::post('/twilio/token', ['as' => 'front.twilio.token', 'uses' => 'Front\TwilioCallController@newToken']);
Route::post('/twilio/support/call', ['as' => 'front.twilio.support-call', 'uses' => 'Front\TwilioCallController@newCall']);