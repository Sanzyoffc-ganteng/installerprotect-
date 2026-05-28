<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SanzyProtectController;

Route::group(['middleware' => ['web', 'auth', 'admin']], function () {
    Route::get('/admin/sanzy-protect', [SanzyProtectController::class, 'index'])
        ->name('admin.sanzy-protect');
    
    Route::post('/admin/sanzy-protect/level', [SanzyProtectController::class, 'updateLevel'])
        ->name('admin.sanzy-protect.level');
    
    Route::post('/admin/sanzy-protect/whitelist', [SanzyProtectController::class, 'toggleWhitelist'])
        ->name('admin.sanzy-protect.whitelist');
});
