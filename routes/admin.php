<?php

use App\Http\Controllers\Admin\InquiryExportController;
use App\Http\Controllers\Admin\SubscriberExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard')->middleware('permission:analytics.view');
        Route::livewire('programs', 'pages::admin.programs.index')->name('programs.index');
        Route::livewire('programs/create', 'pages::admin.programs.form')->name('programs.create');
        Route::livewire('programs/{program}/edit', 'pages::admin.programs.form')->name('programs.edit');
        Route::livewire('programs/{program}/plots', 'pages::admin.plots.index')->name('plots.index');

        Route::livewire('projects', 'pages::admin.projects.index')->name('projects.index');
        Route::livewire('projects/create', 'pages::admin.projects.form')->name('projects.create');
        Route::livewire('projects/{project}/edit', 'pages::admin.projects.form')->name('projects.edit');

        Route::livewire('inquiries', 'pages::admin.inquiries.index')->name('inquiries.index');
        Route::get('inquiries/export', InquiryExportController::class)->name('inquiries.export')->middleware(['permission:inquiries.export', 'throttle:5,1']);

        Route::livewire('users', 'pages::admin.users.index')->name('users.index')->middleware('role:Super Admin');
        Route::livewire('users/create', 'pages::admin.users.form')->name('users.create')->middleware('permission:users.create');

        Route::prefix('cms')->name('cms.')->group(function () {
            Route::livewire('/', 'pages::admin.cms.index')->name('index')->middleware('permission:cms.manage');
            Route::livewire('history', 'pages::admin.cms.history')->name('history')->middleware('permission:cms.manage|seo.manage|theme.manage');
        });

        Route::livewire('testimonials', 'pages::admin.testimonials.index')->name('testimonials.index')->middleware('permission:testimonials.manage');
        Route::livewire('partners', 'pages::admin.partners.index')->name('partners.index')->middleware('permission:partners.manage');

        Route::livewire('posts', 'pages::admin.posts.index')->name('posts.index')->middleware('permission:posts.view');
        Route::livewire('posts/create', 'pages::admin.posts.form')->name('posts.create')->middleware('permission:posts.create');
        Route::livewire('posts/{post}/edit', 'pages::admin.posts.form')->name('posts.edit')->middleware('permission:posts.update');

        Route::livewire('subscribers', 'pages::admin.subscribers.index')->name('subscribers.index')->middleware('permission:subscribers.view');
        Route::get('subscribers/export', SubscriberExportController::class)->name('subscribers.export')->middleware(['permission:subscribers.export', 'throttle:5,1']);

        Route::livewire('analytics', 'pages::admin.analytics.index')->name('analytics.index')->middleware('permission:analytics.view');
    });
