<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LipEyelinerWaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WaitlistController extends Controller
{
    public function lipEyeliner(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email:rfc'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Please enter a valid email address.',
            ], 422);
        }

        // Silent dedup: firstOrCreate returns the existing row if email is
        // already on the list, no unique-constraint error surfaces.
        LipEyelinerWaitlistEntry::firstOrCreate(['email' => $data['email']]);

        return response()->json(['success' => true]);
    }
}