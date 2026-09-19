<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NicknameFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    // Guest local points (localStorage best score etc.) transferred once at
    // registration as a single "guest-import" attempt row - capped so it
    // can't be used to mint arbitrary points via a forged request.
    private const MAX_GUEST_POINTS = 500;

    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nick' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,nick'],
            'pin' => ['required', 'digits_between:4,6', 'confirmed'],
            'is_adult' => ['sometimes', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'required_with:email', 'min:8', 'confirmed'],
            'learner_locale' => ['sometimes', Rule::in(['pl', 'cs', 'sk', 'de'])],
            'guest_points' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (NicknameFilter::isBlocked($validated['nick'])) {
            throw ValidationException::withMessages([
                'nick' => 'Ten nick jest niedozwolony, wybierz inny.',
            ]);
        }

        $user = User::create([
            'nick' => $validated['nick'],
            'pin_hash' => $validated['pin'], // hashed by the `pin_hash => hashed` cast
            'is_adult' => $validated['is_adult'] ?? false,
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'] ?? null,
            'learner_locale' => $validated['learner_locale'] ?? 'pl',
        ]);

        $guestPoints = min((int) ($validated['guest_points'] ?? 0), self::MAX_GUEST_POINTS);
        if ($guestPoints > 0) {
            $user->attempts()->create([
                'course' => 'guest',
                'section' => 'guest-import',
                'exercise_key' => 'migration',
                'correct' => true,
                'points' => $guestPoints,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function createLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'nick' => ['required', 'string'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('nick', $validated['nick'])->first();

        if (! $user || ! Hash::check($validated['pin'], $user->pin_hash)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'nick' => 'Nieprawidłowy nick lub PIN.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'nick' => "Zbyt wiele prób logowania. Spróbuj ponownie za {$seconds} s.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('nick')).'|'.$request->ip();
    }
}
