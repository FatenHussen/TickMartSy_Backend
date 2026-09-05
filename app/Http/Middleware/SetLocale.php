<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Language;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $availableLocales = Language::active()->pluck('code')->toArray();

        $locale = $request->header('Accept-Language');

        if (!in_array($locale, $availableLocales)) {
            $locale = Language::getDefault()?->code ?? 'ar';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
