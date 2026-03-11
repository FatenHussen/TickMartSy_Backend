<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddCsrfTokenToFilament
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // أضف CSRF token إلى الـ response إذا كانت HTML
        if ($response->headers->get('content-type') && str_contains($response->headers->get('content-type'), 'text/html')) {
            $content = $response->getContent();

            // أضف meta tag للـ CSRF token
            $csrfToken = csrf_token();
            $metaTag = "<meta name=\"csrf-token\" content=\"{$csrfToken}\">";

            // أضفه بعد opening head tag
            $content = str_replace('</head>', $metaTag . '</head>', $content);

            $response->setContent($content);
        }

        return $response;
    }
}
