<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['pl', 'cs', 'sk', 'de'];

    /**
     * Resolution order: signed-in user's saved preference, then whatever the
     * session remembers for a guest, then the app default (pl).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->learner_locale
            ?? $request->session()->get('locale')
            ?? config('app.locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
