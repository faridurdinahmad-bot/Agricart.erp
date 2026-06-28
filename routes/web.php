<?php

use App\Livewire\RegisterPage;
use App\Livewire\RegistrationStatusPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', RegisterPage::class)->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/registration/status', RegistrationStatusPage::class)->name('registration.status');
});
