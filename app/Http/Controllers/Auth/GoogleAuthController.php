<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate([
            'google_id' => $googleUser->id,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        // Assign the 'Instructor' role to new users by default.
        if ($user->wasRecentlyCreated) {
            $user->assignRole('Instructor');
        }

        Auth::login($user);

        $request->session()->regenerate();

        // Redirect users based on their role
        if ($user->hasRole(['Admin', 'Super Admin'])) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole('Evaluator')) {
            return redirect()->intended('/evaluator');
        }

        // Default redirect for Instructors
        return redirect()->intended('/instructor');
    }
}
