<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBridgeHmac
{
    /**
     * Maximum age of a signed request, in seconds.
     * Protects against replay attacks while tolerating reasonable clock drift.
     */
    private const TIMESTAMP_WINDOW_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.bridge.hmac_secret');

        if (empty($secret)) {
            return response()->json([
                'success' => false,
                'error' => 'Bridge HMAC secret is not configured.',
            ], 500);
        }

        $timestamp = $request->header('X-Bridge-Timestamp');
        $signature = $request->header('X-Bridge-Signature');

        if (empty($timestamp) || empty($signature)) {
            return response()->json([
                'success' => false,
                'error' => 'Missing bridge authentication headers.',
            ], 401);
        }

        if (!ctype_digit((string) $timestamp)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid bridge timestamp.',
            ], 401);
        }

        $age = abs(time() - (int) $timestamp);
        if ($age > self::TIMESTAMP_WINDOW_SECONDS) {
            return response()->json([
                'success' => false,
                'error' => 'Bridge timestamp out of range.',
            ], 401);
        }

        $canonical = implode("\n", [
            $timestamp,
            strtoupper($request->method()),
            '/' . ltrim($request->path(), '/'),
            $request->getContent(),
        ]);

        $expected = hash_hmac('sha256', $canonical, $secret);

        if (!hash_equals($expected, (string) $signature)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid bridge signature.',
            ], 401);
        }

        return $next($request);
    }
}