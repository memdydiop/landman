<?php

use App\Http\Controllers\PlotPlanController;
use Illuminate\Support\Facades\Route;

Route::get('plots/{plot}/plan', PlotPlanController::class)->name('plots.plan');

Route::livewire('/', 'pages::front.home')->name('home');
Route::livewire('africaspace', 'pages::front.home-africaspace')->name('front.home.africaspace');
Route::livewire('a-propos', 'pages::front.about')->name('front.about');
Route::livewire('services', 'pages::front.services.index')->name('front.services.index');
Route::livewire('services/{service}', 'pages::front.services.show')->name('front.services.show');
Route::livewire('realisations', 'pages::front.projects.index')->name('front.projects.index');
Route::livewire('realisations/{project}', 'pages::front.projects.show')->name('front.projects.show');
Route::livewire('lotissements', 'pages::front.programs.index')->name('front.programs.index');
Route::livewire('lotissements/{program}', 'pages::front.programs.show')->name('front.programs.show');
Route::livewire('actualites', 'pages::front.posts.index')->name('front.posts.index');
Route::livewire('actualites/{post}', 'pages::front.posts.show')->name('front.posts.show');
Route::livewire('contact', 'pages::front.contact')->name('front.contact');
Route::livewire('mentions-legales', 'pages::front.legal.mentions')->name('front.legal.mentions');
Route::livewire('cgv', 'pages::front.legal.cgv')->name('front.legal.cgv');
Route::livewire('confidentialite', 'pages::front.legal.confidentialite')->name('front.legal.confidentialite');
Route::livewire('securite', 'pages::front.legal.securite')->name('front.legal.securite');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()?->can('analytics.view')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
