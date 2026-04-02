<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;


Route::get('/', [App\Http\Controllers\ProductController::class, 'index']);
Route::post('/checkout', [App\Http\Controllers\ProductController::class, 'checkout'])->name('checkout');
Route::get('/success', [App\Http\Controllers\ProductController::class, 'success'])->name('checkout.success');
Route::get('/cancel', [App\Http\Controllers\ProductController::class, 'cancel'])->name('checkout.cancel');
