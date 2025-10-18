<?php

namespace App\Filament\Instructor\Auth;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Login extends Page
{
    /**
     * The view that is used to render the page.
     *
     * @var string
     */
    protected static string $view = 'filament.instructor.auth.login';

    /**
     * The simple layout that is used for authentication pages.
     *
     * @var string
     */
    protected static string $layout = 'filament-panels::components.layout.simple';

    /**
     * This method is called when the page is first mounted.
     * It redirects any authenticated user to the dashboard.
     */
    public function mount(): void
    {
        // If the user is already authenticated, redirect them away from the login page.
        if (Auth::guard('web')->check()) {
            redirect()->intended(config('filament.home_url', '/'));
        }
    }
}
