<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;


Route::get('/', [App\Http\Controllers\ProductController::class, 'index']);
Route::post('/checkout', [App\Http\Controllers\ProductController::class, 'checkout'])->name('checkout');
Route::get('/success', [App\Http\Controllers\ProductController::class, 'success'])->name('checkout.success');
Route::get('/cancel', [App\Http\Controllers\ProductController::class, 'cancel'])->name('checkout.cancel');
Route::post('/webhook', [App\Http\Controllers\ProductController::class, 'webhook'])
    ->withoutMiddleware([
        App\Http\Middleware\VerifyCsrfToken::class,
        Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    ])
    ->name('checkout.webhook');
