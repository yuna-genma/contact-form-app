<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ContactController;

Route::apiResource('v1/contacts', ContactController::class);