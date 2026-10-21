<?php

namespace App\Http\Middleware;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Closure;

class EnsureEmailIsVerified
{
  /**
  * Handle an incoming request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
  * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
  */
  public function handle($request, Closure $next)
  {
    if (! auth()->guard('api')->user() ||
    (auth()->guard('api')->user() instanceof MustVerifyEmail &&
    ! auth()->guard('api')->user()->hasVerifiedEmail())) {
      return response()->json([
        'message' => 'Your email address is not verified.',
      ], 403);
    }
    return $next($request);
  }
}
