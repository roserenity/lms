<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        $email = $request->input('email'); // Replace with your request parameter to get the email address

        if ($this->limiter->tooManyAttempts($this->throttleKey($request, $email), $maxAttempts)) {
            return $this->buildResponse($maxAttempts);
        }

        $this->limiter->hit($this->throttleKey($request, $email), $decayMinutes * 60);

        return $next($request);
    }

    protected function throttleKey(Request $request, $email)
    {
        return 'custom_email_rate_limit:' . $email . '|' . $request->ip();
    }

    protected function buildResponse($maxAttempts)
    {
        return response()->json([
            'message' => 'Too many login attempts. Please try again later.',
            'errors' => [
                'status' => ['Too many login attempts. Please try again later.'],
            ]
        ], 429); // You can customize the response as needed
    }
}
