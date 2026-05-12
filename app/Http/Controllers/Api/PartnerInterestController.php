<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartnerInterestEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartnerInterestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email:rfc'],
                'shop_name' => ['required', 'string', 'max:255'],
                'shop_location' => ['required', 'string', 'max:255'],
                'procedure_focus' => ['required', 'in:tattoo,pmu,both'],
                'source_page' => ['required', 'in:tattoo,pmu'],
                'social_handle' => ['nullable', 'string', 'max:255'],
                'how_did_you_hear' => ['nullable', 'string', 'max:5000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Please complete all required fields.',
            ], 422);
        }

        $entry = PartnerInterestEntry::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'shop_name' => $data['shop_name'],
                'shop_location' => $data['shop_location'],
                'procedure_focus' => $data['procedure_focus'],
                'source_page' => $data['source_page'],
                'social_handle' => $data['social_handle'] ?? null,
                'how_did_you_hear' => $data['how_did_you_hear'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'duplicate' => ! $entry->wasRecentlyCreated,
        ]);
    }
}
