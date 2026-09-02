<?php

declare(strict_types=1);

namespace App\Livewire\Front;

use App\Models\Subscriber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $email = '';

    public string $name = '';

    public string $website = ''; // honeypot — doit rester vide

    public bool $done = false;

    public function submit(): void
    {
        // Honeypot
        if ($this->website !== '') {
            $this->done = true;

            return;
        }

        // Rate limit 3/min par IP
        $key = 'newsletter:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).'s.');

            return;
        }
        RateLimiter::hit($key, 60);

        $validated = $this->validate([
            'email' => ['required', 'email', 'max:255', 'unique:subscribers,email'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        Subscriber::create($validated);

        RateLimiter::clear($key);
        $this->done = true;
        $this->reset(['email', 'name', 'website']);
    }

    public function render(): View
    {
        return view('livewire.front.newsletter-form');
    }
}
