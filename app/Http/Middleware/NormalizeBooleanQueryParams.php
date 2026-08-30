<?php

namespace App\Http\Middleware;

use App\Support\BooleanQueryNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeBooleanQueryParams
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->query->count() > 0) {
            $request->query->replace(
                BooleanQueryNormalizer::normalize($request->query->all())
            );
        }

        return $next($request);
    }
}
