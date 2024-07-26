<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Rotte per la sottoscrizione
    Route::get('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

    // Rotte protette che richiedono una sottoscrizione attiva
    Route::middleware(['check.status'])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Qui puoi aggiungere altre rotte che richiedono una sottoscrizione attiva
        // Per esempio:
        // Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        // Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        // Route::get('/artworks', [ArtworkController::class, 'index'])->name('artworks.index');
        // Route::post('/artworks', [ArtworkController::class, 'store'])->name('artworks.store');
        Route::get('/subscription/change', [SubscriptionController::class, 'showChangePlan'])->name('subscription.change');
        Route::post('/subscription/change', [SubscriptionController::class, 'changePlan']);
    });

    Route::middleware(['auth', 'is.admin'])->group(function () {
        Route::get('/admin/payments', [App\Http\Controllers\Admin\DashboardController::class, 'payments'])->name('admin.payments');
        
    });
});

// Rotta pubblica per la pagina dell'artista
Route::get('/{slug}', [ArtistController::class, 'show'])->name('artist.show');

Route::get('/admin/invoice/{invoice}/download', [App\Http\Controllers\Admin\DashboardController::class, 'downloadInvoice'])->name('admin.invoice.download');