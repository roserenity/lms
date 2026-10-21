<?php

namespace App\Http\Middleware;

use Closure;

class Authenticate
{
  /**
  * Get the path the user should be redirected to when they are not authenticated.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return string|null
  */
  public function handle($request, Closure $next)
  {
    if(!auth()->guard('api')->check()) {
      return response()->json([
        'message' => 'Unauthenticated.',
      ], 403);
    }
    return $next($request);
  }
}
