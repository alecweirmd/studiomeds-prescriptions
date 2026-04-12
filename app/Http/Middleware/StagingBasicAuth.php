<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StagingBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() !== 'staging.prescriptions.studiomeds.com') {
            return $next($request);
        }

        $username = env('STAGING_USERNAME');
        $password = env('STAGING_PASSWORD');

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
