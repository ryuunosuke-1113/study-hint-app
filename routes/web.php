<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudyHintController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\BookController;

Route::resource('study-hints', StudyHintController::class);
Route::get('/', function () {
    return redirect('/study-hints');
});
Route::resource('subjects', SubjectController::class);
Route::resource('books', BookController::class);