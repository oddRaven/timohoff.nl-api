<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TimelineController;
use App\Http\Controllers\WaypointController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\SectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('authentication', [AuthenticationController::class, 'login']);
Route::delete('authentication', [AuthenticationController::class, 'logout']);

Route::get('section', [SectionController::class, 'index']);
Route::get('section/{id}', [SectionController::class, 'find']);
Route::middleware('auth')->post('section', [SectionController::class, 'store']);
Route::middleware('auth')->put('section/{id}', [SectionController::class, 'update']);
Route::middleware('auth')->delete('section/{id}', [SectionController::class, 'delete']);

Route::get('timeline/{id}', [TimelineController::class, 'find']);

Route::get('waypoint', [WaypointController::class, 'index']);
Route::get('waypoint/{id}', [WaypointController::class, 'find']);