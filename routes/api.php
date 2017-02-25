<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Http\Request;
use Telegram;

Route::get('/get-message', function (Request $request) {
    
    logger("message request : ", $request->all());
});

Route::post('/get-message', ['as' => 'line.bot.message', 'uses' => 'GetMessageController@getMessage']);

Route::post('/test', ['as' => 'line.bot.message', 'uses' => 'GetMessageController@test']);


Route::post('/<token>/webhook', function () {
    $updates = Telegram::getWebhookUpdates();

    return 'ok';
});