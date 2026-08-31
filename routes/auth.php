<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/*
 * POS'da foydalanuvchi o'zi ro'yxatdan o'tmaydi — hisobni administrator
 * ochadi. Shuning uchun register / parolni tiklash / email tasdiqlash
 * marshrutlari olib tashlandi: ular hech qachon ishlatilmagan, lekin
 * mavjud bo'lmagan view'larga ishora qilardi.
 */
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
