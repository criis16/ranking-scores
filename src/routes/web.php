<?php

use Illuminate\Support\Facades\Route;
use App\User\Controllers\NewUserController;

Route::post('/user/{userId}/score', NewUserController::class);
