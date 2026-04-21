<?php

use App\Http\Controllers\ContactFormController;
use App\Support\StaticPageRenderer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response(StaticPageRenderer::render('home')))->name('home');
Route::post('/kontakt', ContactFormController::class)->name('contact.submit');
Route::get('/datenschutz', fn () => response(StaticPageRenderer::render('datenschutz')))->name('datenschutz');
Route::get('/impressum', fn () => response(StaticPageRenderer::render('impressum')))->name('impressum');
Route::get('/static/{path}', function (string $path) {
    abort_unless(
        Str::startsWith($path, ['wp-content/', 'wp-includes/', 'external/', 'assets/', 'local-assets/']),
        404
    );

    $normalizedPath = str_replace(['../', '..\\'], '', $path);
    $filePath = public_path($normalizedPath);

    abort_unless(is_file($filePath), 404);

    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $contentType = match ($extension) {
        'css' => 'text/css; charset=utf-8',
        'js', 'mjs' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'font/otf',
        'webp' => 'image/webp',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'mp4' => 'video/mp4',
        default => File::mimeType($filePath) ?: 'application/octet-stream',
    };

    return response()->file($filePath, [
        'Content-Type' => $contentType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('static.asset');
