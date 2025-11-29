<?php

use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // ウォレット作成
    Route::post('/wallet/create', [WalletController::class, 'createWalletOnRegistration']);
    
    // ウォレット情報取得
    Route::get('/wallet', [WalletController::class, 'getWallet']);
});
