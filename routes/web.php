<?php

use App\Http\Controllers\PollController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;


Route::get('/', [PollController::class, 'index'])->name('polls.index');

Route::get('/polls/{poll:slug}', [PollController::class, 'show'])->name('polls.show');

Route::post('/polls/{poll}/vote', [VoteController::class, 'store'])
    ->name('polls.vote')
    ->middleware('throttle:5,1');


