<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TradeController;

Route::get('/', [TradeController::class, 'index'])->name('trades.index');
Route::get('/upload', [TradeController::class, 'create'])->name('trades.create');
Route::post('/upload', [TradeController::class, 'store'])->name('trades.store');

Route::get('/trades/{id}/edit', [TradeController::class, 'edit'])->name('trades.edit');
Route::put('/trades/{id}', [TradeController::class, 'update'])->name('trades.update');