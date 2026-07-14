<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

// Laravel's default user route (you can leave this here)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Our Transaction Endpoints
Route::post('/transactions', [TransactionController::class, 'store']);
Route::post('/transactions/{id}/approve', [TransactionController::class, 'approve']);
Route::post('/transactions/{id}/return', [TransactionController::class, 'markAsReturned']);