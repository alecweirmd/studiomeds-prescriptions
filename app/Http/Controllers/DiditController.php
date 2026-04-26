<?php

namespace App\Http\Controllers;

use App\Models\Patients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiditController extends Controller
{
    public function createSession(Request $request)
    {
        $patientId = $request->input('patient_id');

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.didit.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://verification.didit.me/v3/session/', [
                'workflow_id' => config('services.didit.workflow_id'),
                'vendor_data'  => (string) $patientId,
            ]);

            if (!$response->successful()) {
                Log::error('Didit session creation failed: ' . $response->body());
                return response()->json(['error' => 'Failed to create verification session.'], 500);
            }

            $data = $response->json();

            return response()->json([
                'verification_url' => $data['url'] ?? $data['verification_url'] ?? null,
                'session_id'       => $data['session_id'] ?? $data['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Didit session exception: ' . $e->getMessage());
            return response()->json(['error' => 'Verification service unavailable.'], 500);
        }
    }

    public function checkStatus(Request $request)
    {
        $patient = Patients::find($request->input('patient_id'));

        if ($patient && $patient->verification_method === 'didit') {
            return response()->json(['verified' => true]);
        }

        return response()->json(['verified' => false]);
    }

    public function webhook(Request $request)
    {
        $secret    = config('services.didit.webhook_secret');
        $signature = $request->header('X-Signature');
        $payload   = $request->all();

        if ($secret && $signature) {
            $rawBody     = $request->getContent();
            $expectedSig = hash_hmac('sha256', $rawBody, $secret);
            if (!hash_equals($expectedSig, $signature)) {
                Log::warning('Didit webhook signature mismatch.');
                return response()->json(['ok' => false], 200);
            }
        }

        $status    = $payload['status'] ?? null;
        $sessionId = $payload['session_id'] ?? null;
        $patientId = $payload['vendor_data'] ?? null;

        if ($status === 'Approved' && $patientId) {
            $patient = Patients::find($patientId);
            if ($patient) {
                $patient->verification_method = 'didit';
                $patient->didit_session_id    = $sessionId;
                $patient->save();
            }
        }

        return response()->json(['ok' => true], 200);
    }
}
