<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('registro',  [RegisteredUserController::class, 'create'])->name('register');
    Route::post('registro', [RegisteredUserController::class, 'store'])
         ->middleware('throttle:6,1');

    Route::get('acceder',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('acceder', [AuthenticatedSessionController::class, 'store'])
         ->middleware('throttle:6,1');

    Route::get('olvide-contrasena',  [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('olvide-contrasena', [PasswordResetLinkController::class, 'store'])
         ->middleware('throttle:6,1')->name('password.email');

    Route::get('restablecer/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('restablecer', [NewPasswordController::class, 'store'])
         ->middleware('throttle:6,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('salir', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
