<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\Api\WaRuntimeController;
use App\Http\Controllers\AddressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Default user endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp webhook endpoints (public, no sanctum)
Route::post('/whatsapp/incoming', [WhatsAppController::class, 'incoming']);
Route::post('/whatsapp/send', [WhatsAppController::class, 'send']);


Route::post('/wa/incoming', [WaRuntimeController::class, 'incoming']);

// Address search API
Route::get('/address/search', [AddressController::class, 'search']);
Route::get('/address/search-vlaanderen', [AddressController::class, 'searchVlaanderen']);

