<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->name('form.show');
Route::post('/contacts/confirm', [ContactController::class, 'store'])->name('form.confirm');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('form.thanks');