<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TradeController;

// Halaman input trade
Route::get('/entry', [TradeController::class, 'create'])->name('trades.create');
Route::post('/entry', [TradeController::class, 'store'])->name('trades.store');

// Rute untuk update status trade (WIN/LOSS)
Route::post('/trade/{id}/update', [TradeController::class, 'updateStatus'])->name('trades.updateStatus');

Route::delete('/trade/{id}', [TradeController::class, 'destroy'])->name('trades.destroy');

// Redirect halaman depan ke form entry (opsional)
Route::get('/', function () {
    return redirect()->route('trades.create');
});