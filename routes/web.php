<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/deposit', [TransactionController::class, 'showDepositForm'])->name('deposit.form');
    Route::post('/deposit', [TransactionController::class, 'deposit'])->name('deposit');

    Route::get('/withdraw', [TransactionController::class, 'showWithdrawForm'])->name('withdraw.form');
    Route::post('/withdraw', [TransactionController::class, 'withdraw'])->name('withdraw');

    Route::get('/admin/transactions', [AdminController::class, 'showTransactions'])->name('admin.transactions');
});

require __DIR__.'/auth.php';
