<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('user.')->prefix('user')->group(function () {
    Route::middleware('guest')->post('', [UserController::class, 'create'])->name('create');
});

Route::name('auth.')->prefix('auth')->group(function () {
    Route::middleware('guest')->post('/login', [AuthController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth:sanctum')->name('transaction.')->prefix('transaction')->group(function () {
    Route::post('/deposit', [TransactionController::class, 'deposit'])->name('deposit');
    Route::post('/purchase', [TransactionController::class, 'purchase'])->name('purchase');
    Route::get('/', [TransactionController::class, 'get'])->name('list');
    Route::name('admin.')->prefix('admin')->group(function () {
        Route::post('/finish-deposit/{transactionId}', [TransactionController::class, 'finishDeposit'])->name('finish-deposit');
        Route::get('/pending-deposits', [TransactionController::class, 'pendingDeposits'])->name('pending-deposits');
    });
});


Route::get('/', function () {
    return app()->version();
});
