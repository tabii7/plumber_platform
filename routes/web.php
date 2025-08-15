<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlumberController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ClientRequestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PostcodeController;
// use App\Http\Controllers\AdminWhatsappController;
use App\Http\Controllers\PlumberCoverageController;
use App\Http\Controllers\PlumberCategoryController;
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
});

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

    Route::resource('/requests', ClientRequestController::class);

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


     Route::get('/plumber/categories', [PlumberCategoryController::class, 'edit'])
        ->name('plumber.categories.edit');
    Route::post('/plumber/categories', [PlumberCategoryController::class, 'update'])
        ->name('plumber.categories.update');


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

    // Requests (admin)
    Route::resource('/admin/requests', RequestController::class)
        ->names('admin.requests');

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
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
