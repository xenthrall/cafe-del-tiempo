<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.pages.home')->name('home');

Route::view('/docs/instalacion', 'landing.docs.installation')->name('docs.installation');

Route::get('/sitemap.xml', function () {
    return response()
        ->view('landing.sitemap')
        ->header('Content-Type', 'application/xml');
})->name('sitemap');
