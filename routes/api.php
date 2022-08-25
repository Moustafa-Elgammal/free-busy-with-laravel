<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelApi\Facade as Api;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Api::post('check', [\App\Http\Controllers\Api\MeetingsController::class, 'getTimeSlotsApi']) ->setSummary('list of time slots')
    ->setDescription('Get list of valid time slots to create a meeting')
    ->addTag('get slots')
    ->setOperationId('executeAction')
    ->addBodyParameter('body', 'Please use the same shape shown in readme.md file',true,)
    ->addScheme('body',)
    ->setConsumes(['application/json'])
    ->setProduces(['application/json']);
