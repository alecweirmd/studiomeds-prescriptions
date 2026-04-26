<?php

namespace App\Http\Controllers;

use App\Models\Patients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

                $decision   = $payload['decision'] ?? [];
                $idChecks   = $decision['id_verifications'] ?? [];
                $liveness   = $decision['liveness_checks'] ?? [];

                $frontUrl   = $idChecks[0]['front_image'] ?? null;
                $backUrl    = $idChecks[0]['back_image'] ?? null;
                $selfieUrl  = $liveness[0]['reference_image'] ?? null;

                $baseDir    = "uploads/{$patient->id}/didit";

                $frontPath  = $this->downloadDiditImage($frontUrl,  "{$baseDir}/front.jpg",  $patient->id, 'front');
                $this->downloadDiditImage($backUrl, "{$baseDir}/back.jpg", $patient->id, 'back');
                $selfiePath = $this->downloadDiditImage($selfieUrl, "{$baseDir}/selfie.jpg", $patient->id, 'selfie');

                if ($frontPath)  { $patient->drivers_license = $frontPath; }
                if ($selfiePath) { $patient->patient_photo   = $selfiePath; }

                $patient->save();
            }
        }

        return response()->json(['ok' => true], 200);
    }

    private function downloadDiditImage(?string $url, string $storagePath, $patientId, string $label): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::error("Didit image download failed for patient {$patientId} ({$label}): HTTP " . $response->status());
                return null;
            }

            Storage::disk('public')->put($storagePath, $response->body());
            return $storagePath;
        } catch (\Exception $e) {
            Log::error("Didit image download exception for patient {$patientId} ({$label}): " . $e->getMessage());
            return null;
        }
    }
}
