<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestContext
{
    /**
     * Handle an incoming request and add context to logs
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add context to all log messages during this request
        Log::withContext([
            'environment' => config('app.env'),
            'release_version' => config('app.release_version', 'unknown'),
            'release_commit' => config('app.release_commit', 'unknown'),
            'user_id' => $request->user()?->id,
            'request_id' => (string) \Illuminate\Support\Str::uuid(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
