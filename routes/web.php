<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlumberController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PostcodeController;
// use App\Http\Controllers\AdminWhatsappController;
use App\Http\Controllers\PlumberCoverageController;
use App\Http\Controllers\PlumberCategoryController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\WaFlowController;
use App\Http\Controllers\Admin\WaNodeController;
use App\Http\Controllers\Admin\WhatsAppController as AdminWhatsAppController;
use App\Http\Controllers\Admin\ClientController;


use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Postal Code search + radius
Route::get('/zoek-postcode', [PostcodeController::class, 'zoek'])->name('postcode.search');
Route::get('/werk-radius', [PostcodeController::class, 'radius'])->name('postcode.radius');









// Role-based redirect after login (central dashboard)
Route::get('/redirect-dashboard', function () {
    $role = Auth::user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'plumber') {
        return redirect()->route('plumber.dashboard');
    }
    return redirect()->route('client.dashboard');
})->middleware('auth')->name('redirect.dashboard');

// Generic dashboard alias so controllers can safely redirect
Route::get('/dashboard', function () {
    return redirect()->route('redirect.dashboard');
})->middleware('auth')->name('dashboard');







// Client dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/client/dashboard', function () {
        return view('dashboards.client');
    })->name('client.dashboard');


    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});





// Plumber dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/plumber/dashboard', function () {
        return view('dashboards.plumber');
    })->name('plumber.dashboard');



    Route::post('/plumber/status', function (\Illuminate\Http\Request $request) {
    $request->validate(['status' => 'required|in:available,busy,holiday']);
    $user = \Illuminate\Support\Facades\Auth::user();
    $user->status = $request->status;
    $user->save();

    return back()->with('success', 'Status updated to ' . ucfirst($request->status));
})->middleware(['auth'])->name('plumber.status.update');


    Route::get('/plumber/coverage',        [PlumberCoverageController::class, 'index'])->name('plumber.coverage.index');
    Route::post('/plumber/coverage',       [PlumberCoverageController::class, 'store'])->name('plumber.coverage.store');
    Route::delete('/plumber/coverage/{id}',[PlumberCoverageController::class, 'destroy'])->name('plumber.coverage.destroy');

    // AJAX helpers
    Route::get('/municipalities/search',   [PlumberCoverageController::class, 'searchMunicipalities'])->name('municipalities.search');
    Route::get('/municipalities/{name}/towns', [PlumberCoverageController::class, 'municipalityTowns'])->name('municipalities.towns');
    Route::get('/municipalities/nearby',   [PlumberCoverageController::class, 'nearbyMunicipalities'])->name('municipalities.nearby');
    Route::post('/plumber/coverage/bulk',  [PlumberCoverageController::class, 'bulkStore'])->name('plumber.coverage.bulk');


     Route::get('/plumber/categories', [PlumberCategoryController::class, 'edit'])
        ->name('plumber.categories.edit');
    Route::post('/plumber/categories', [PlumberCategoryController::class, 'update'])
        ->name('plumber.categories.update');

    // Schedule management
    Route::get('/plumber/schedule', [App\Http\Controllers\PlumberScheduleController::class, 'index'])
        ->name('plumber.schedule.index');
    Route::post('/plumber/schedule', [App\Http\Controllers\PlumberScheduleController::class, 'update'])
        ->name('plumber.schedule.update');
    Route::get('/plumber/schedule/api', [App\Http\Controllers\PlumberScheduleController::class, 'getSchedule'])
        ->name('plumber.schedule.api');
    Route::post('/plumber/schedule/availability', [App\Http\Controllers\PlumberScheduleController::class, 'checkAvailability'])
        ->name('plumber.schedule.availability');


});





// Request Management Routes (for all authenticated users)
Route::middleware(['auth'])->group(function () {
    // WaRequest routes
    Route::get('/requests', [App\Http\Controllers\WaRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{request}', [App\Http\Controllers\WaRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{request}/complete', [App\Http\Controllers\WaRequestController::class, 'complete'])->name('requests.complete');
    Route::get('/requests/stats', [App\Http\Controllers\WaRequestController::class, 'getStats'])->name('requests.stats');
});

// Admin routes 
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    // Plumbers
    Route::resource('/admin/plumbers', PlumberController::class)
        ->names('plumbers');

    // Clients
    Route::resource('/admin/clients', ClientController::class)
        ->only(['index','show','edit','update','destroy'])
        ->names('clients');


    // Admin WaRequest management
    Route::post('/requests/{waRequest}/update-status', [App\Http\Controllers\WaRequestController::class, 'updateStatus'])->name('requests.update-status');

    // WhatsApp (use the controller class you actually created)
    Route::get('/admin/whatsapp',        [AdminWhatsAppController::class, 'index'])->name('admin.whatsapp');
    Route::get('/admin/whatsapp/qr',     [AdminWhatsAppController::class, 'qr'])->name('admin.whatsapp.qr');
    Route::get('/admin/whatsapp/status', [AdminWhatsAppController::class, 'status'])->name('admin.whatsapp.status');
    Route::post('/admin/whatsapp/test-send', [AdminWhatsAppController::class, 'testSend'])->name('admin.whatsapp.testSend');

    // Flows + nested Nodes (custom messages)
       Route::resource('admin/flows', WaFlowController::class)->names('admin.flows');
    Route::resource('admin/flows.nodes', WaNodeController::class)->names('admin.flows.nodes');
});


// Include Laravel Breeze / Fortify routes
require __DIR__.'/auth.php';

// ✅ Custom registration routes overriding Breeze defaults
// Main register route redirects to client registration only
Route::get('/register', function() {
    return redirect()->route('client.register');
})->name('register');

// Separate registration routes for clients and plumbers
Route::get('/client/register', [App\Http\Controllers\Auth\ClientRegistrationController::class, 'create'])->name('client.register');
Route::post('/client/register', [App\Http\Controllers\Auth\ClientRegistrationController::class, 'store'])->name('client.register.store');

Route::get('/plumber/register', [App\Http\Controllers\Auth\PlumberRegistrationController::class, 'create'])->name('plumber.register');
Route::post('/plumber/register', [App\Http\Controllers\Auth\PlumberRegistrationController::class, 'store'])->name('plumber.register.store');

// Checkout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
});

// Webhook route (no auth required)
Route::post('/checkout/webhook', [App\Http\Controllers\CheckoutController::class, 'webhook'])->name('checkout.webhook');

// Support page for WhatsApp menu
Route::get('/support', function () {
    return view('support');
})->middleware(['auth'])->name('support');

// Test pages
Route::get('/test-register', function () {
    return view('test-register');
});

Route::get('/test-address', function () {
    return view('test-address');
});

Route::get('/test-dark-mode', function () {
    return view('test-dark-mode');
});


