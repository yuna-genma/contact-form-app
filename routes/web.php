<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->name('form.show');
Route::post('/contacts/confirm', [ContactController::class, 'store'])->name('form.confirm');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('form.thanks');

Route::middleware('auth')->group(function () {
    Route::get('/admin', fn() => '管理画面（準備中）');
    Route::get('/admin/contacts/{contact}', fn() => 'お問い合わせ詳細ページ（準備中）');
    Route::post('/admin', [TagController::class, 'store']);
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit']);
    Route::put('/admin/tags/{tag}/edit', [TagController::class, 'update']);
    Route::delete('/admin', [TagController::class, 'destroy']);
});