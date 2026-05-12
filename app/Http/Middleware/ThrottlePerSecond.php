<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePerSecond
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 2, string $scope = 'global'): Response
    {
        $user = $request->user();
        $key = ($user?->id ? "user:{$user->id}" : "ip:{$request->ip()}");
        $key = "ps:{$scope}:{$key}";

        // 1-second decay for burst protection
        $decaySeconds = 1;

        if (RateLimiter::tooManyAttempts($key, max(1, $maxAttempts))) {
            $retryAfter = RateLimiter::availableIn($key);
            $this->logRateLimitHit($request, $scope, (int) $maxAttempts, 0, (int) $retryAfter);

            return response()->json([
                'message' => 'Too Many Requests',
                'limit' => (int) $maxAttempts,
                'retry_after_seconds' => (int) $retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        /** @var Response $response */
        $response = $next($request);

        $remaining = max(0, max(1, $maxAttempts) - RateLimiter::attempts($key));
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    private function logRateLimitHit(Request $request, string $scope, int $limit, int $remaining, int $retryAfter): void
    {
        try {
            DB::table('api_rate_limits')->insert([
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'endpoint' => $request->path(),
                'scope' => $scope,
                'limit_value' => $limit,
                'remaining' => $remaining,
                'retry_after' => $retryAfter,
                'hit_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Don't break request handling when monitoring table is unavailable.
            logger()->warning('api_rate_limits insert failed', ['error' => $e->getMessage()]);
        }
    }
}

