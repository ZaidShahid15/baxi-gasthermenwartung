<?php

use App\Http\Controllers\ContactFormController;
use App\Support\StaticPageRenderer;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response(StaticPageRenderer::render('home')))->name('home');
Route::post('/kontakt', ContactFormController::class)->name('contact.submit');
Route::get('/datenschutz', fn () => response(StaticPageRenderer::render('datenschutz')))->name('datenschutz');
Route::get('/impressum', fn () => response(StaticPageRenderer::render('impressum')))->name('impressum');
