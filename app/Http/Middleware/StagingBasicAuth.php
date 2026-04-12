<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StagingBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.env') !== 'staging') {
            return $next($request);
        }

        $username = config('app.staging_username');
        $password = config('app.staging_password');

        if (
            $request->getUser() !== $username ||
            $request->getPassword() !== $password
        ) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Staging"',
            ]);
        }

        return $next($request);
    }
}
