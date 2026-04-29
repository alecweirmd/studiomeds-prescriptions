<?php

namespace App\Http\Controllers;

use App\Models\MaintStates;
use App\Models\User;
use App\Models\PatientsCQI;
use App\Models\Patients;
use App\Models\UtmVisit;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Services\AuthorizeNetService;
use App\Models\PatientAcknowledgement;
use App\Models\FormStart;
use Barryvdh\DomPDF\Facade\Pdf;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Illuminate\Support\Facades\Http;
use Storage;
use Session;
use DateTime;
use Auth;
use DB;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{

    public function artist_register()
    {
        abort(404);

        $data['states'] = MaintStates::get();

        return view('users/artist_registration', $data);
    }

    public function store_artist(Request $request)
    {
        abort(404);
        $validated = $this->validateArtistRequest($request);

        try {
            // 1️⃣ Create the user
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'artist_name' => $validated['artist_name'] ?? null,
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'name_of_shop' => $validated['name_of_shop'] ?? null,
                'street_address' => $validated['street_address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'user_type' => 2,
                'uuid' => Str::uuid(36)
            ]);

            $driversLicensePath = null;
            $selfiePath = null;

            if ($request->hasFile('drivers_license')) {
                $driversLicensePath = $request->file('drivers_license')
                    ->store("uploads/{$user->id}/drivers_license", 'public');
            }

            if ($request->hasFile('selfie_photo')) {
                $selfiePath = $request->file('selfie_photo')
                    ->store("uploads/{$user->id}/selfie", 'public');
            }


            $user->update([
                'drivers_license' => $driversLicensePath,
                'selfie_photo' => $selfiePath,
            ]);

            // 3️⃣ Create subscription via server-side Authorize.net service
            $subscriptionService = new AuthorizeNetService();

            // For server-side tokenless flow, you need card details securely
            $subscriptionId = $subscriptionService->createSubscriptionFromRequest($user, $request);

            $user->update(['subscription_id' => $subscriptionId]);

            return redirect()->back()->with('success', 'Artist registered and subscription created.');
        } catch (\Exception $e) {
            Log::error('Artist registration failed: ' . $e->getMessage(), ['exception' => $e]);

            if (isset($user)) {
                $user->delete();
            }

            return redirect()->back()->withErrors('Registration failed. Please try again or contact support.');
        }
    }

    /**
     * Validate Data
     * @return type
     */
    protected function validateArtistRequest(Request $request)
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'artist_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|max:255',
            'password' => 'required|string|min:6',
            'name_of_shop' => 'nullable|string|max:255',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required',
            'zip' => 'required|string|max:20',
            'drivers_license' => 'nullable|image|max:10240',
            'selfie_photo' => 'nullable|image|max:10240',
        ]);
    }

    /**
     * Validate Data
     * @return type
     */
    protected function validatePateintRequest(Request $request)
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'street_address' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email',
            'artist_name' => 'string|max:255',
            'name_of_shop' => 'string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required',
            'zip' => 'required|string|max:10',
            'drivers_license_image' => 'nullable|image|max:10240',
            'selfie_image' => 'nullable|image|max:10240',
            // Allergies / Sensitivities
            'lidocaine' => 'required|boolean',
            'bactine' => 'required|boolean',
            // Skin Conditions
            'broken_skin' => 'required|boolean',
            'eczema' => 'required|boolean',
            // Medical History
            'heart_rhythm' => 'required|boolean',
            'liver_disease' => 'required|boolean',
            'seizures' => 'required|boolean',
            'pregnant' => 'required|boolean',
            // Medications
            'antiarrhythmic' => 'required|boolean',
            'seizure_meds' => 'required|boolean',
            // Past Reactions
            'fainted' => 'required|boolean',
            'methemoglobinemia' => 'required|boolean',
        ]);
    }

    public function client_form($uuid = null)
    {
        if ($uuid != null) {
            $data['artist'] = User::where('uuid', $uuid)->first();
        } else {
            $data['artist'] = null;
        }
        $data['states'] = MaintStates::get();
        return view('users/patient_registration', $data);
    }

    public function store_patient(Request $request)
    {

        if (app()->environment('production')) {
            $recaptchaToken = $request->input('recaptcha_token');

            if (!$recaptchaToken) {
                return back()->withErrors([
                    'captcha' => 'Captcha verification failed.'
                ])->withInput();
            }

            try {
                $response = Http::timeout(5)->asForm()->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret'   => config('services.recaptcha.secret_key'),
                        'response' => $recaptchaToken,
                        'remoteip' => $request->ip(),
                    ]
                );
                $captchaData = $response->json();
            } catch (\Exception $e) {
                Log::warning('reCAPTCHA verification unavailable: ' . $e->getMessage());
                $captchaData = null;
            }

            if ($captchaData !== null) {
                Log::info('reCAPTCHA result - score: ' . ($captchaData['score'] ?? 'n/a') . ', action: ' . ($captchaData['action'] ?? 'n/a') . ', success: ' . (($captchaData['success'] ?? false) ? 'true' : 'false') . ', hostname: ' . ($captchaData['hostname'] ?? 'n/a'));

                if (
                    !($captchaData['success'] ?? false) ||
                    ($captchaData['score'] ?? 0) < config('services.recaptcha.score_threshold') ||
                    ($captchaData['action'] ?? '') !== 'submit_patient'
                ) {
                    return back()->withErrors([
                        'captcha' => 'Suspicious activity detected. Please try again.'
                    ])->withInput();
                }
            } else {
                Log::warning("reCAPTCHA skipped for patient submission (service unavailable), IP: {$request->ip()}");
            }
        }

        $validStates = [
            'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut',
            'Delaware','District of Columbia','Florida','Georgia','Hawaii','Idaho','Illinois',
            'Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts',
            'Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada',
            'New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota',
            'Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina',
            'South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington',
            'West Virginia','Wisconsin','Wyoming',
        ];

        $medicalFields = ['lidocaine','bactine','broken_skin','eczema','heart_rhythm','liver_disease','seizures','pregnant','antiarrhythmic','seizure_meds','fainted','methemoglobinemia'];

        $medicalValidation = [];
        foreach ($medicalFields as $field) {
            $medicalValidation[$field] = 'required|in:0,1';
        }

        $diditVerified = (int) $request->input('didit_verified', 0);
        $imageRule = $diditVerified === 1 ? 'nullable|image|max:10240' : 'required|image|max:10240';

        $request->validate(array_merge([
            'email'                 => ['required', 'email'],
            'zip'                   => ['required', 'regex:/^\d{5}(-\d{4})?$/'],
            'state'                 => ['required', 'string', \Illuminate\Validation\Rule::in($validStates)],
            'date_of_birth'         => ['required', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYears(18)->toDateString(), 'after:1000-01-01', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'drivers_license_image' => $imageRule,
            'selfie_image'          => $imageRule,
        ], $medicalValidation), [
            'email.email'                        => 'Please enter a valid email address.',
            'zip.regex'                          => 'Please enter a valid US ZIP code (e.g. 12345 or 12345-6789).',
            'state.in'                           => 'Please select a valid US state from the list.',
            'date_of_birth.before_or_equal'      => 'You must be 18 or older to submit this form.',
            'drivers_license_image.required'     => 'Please upload a photo of your driver\'s license.',
            'selfie_image.required'              => 'Please upload a selfie photo.',
        ]);

        // Block submission if any medical question was answered Yes
        foreach ($medicalFields as $field) {
            if ((int) $request->input($field) === 1) {
                return back()->withErrors([
                    'medical' => 'Based on your medical history, please see an in-person provider for a prescription for topical anesthetics.',
                ])->withInput();
            }
        }

        $patient = Patients::find($request->patient_id);

        if (!$patient) {
            return back()->withErrors(['patient' => 'Something went wrong.  Please refresh the page and try again.'])->withInput();
        }

        // ── Resolve referral / discount code (server-side authoritative) ──
        $appliedCodeRaw = trim((string) $request->input('applied_code', ''));
        $resolvedCode   = null;
        $isFreeFlow     = false;
        $discountAmount = 0.00;
        $finalAmount    = 35.00;

        if ($appliedCodeRaw !== '') {
            $resolvedCode = DiscountCode::whereRaw('LOWER(code_string) = ?', [strtolower($appliedCodeRaw)])->first();

            if (!$resolvedCode || $resolvedCode->status !== 'active' ||
                $resolvedCode->usage_count >= $resolvedCode->usage_cap ||
                ($resolvedCode->expiration_date && $resolvedCode->expiration_date->isBefore(now()->startOfDay()))) {

                if ($resolvedCode) {
                    DiscountRedemption::create([
                        'discount_code_id' => $resolvedCode->id,
                        'session_id'       => $request->input('utm_session_id'),
                        'attempt_outcome'  => $resolvedCode->status === 'expired'
                            ? 'failed_expired'
                            : ($resolvedCode->usage_count >= $resolvedCode->usage_cap ? 'failed_exhausted' : 'failed_invalid'),
                    ]);
                }
                return back()->withErrors(['applied_code' => 'The referral code is no longer valid. Please remove it and try again.'])->withInput();
            }

            if ($resolvedCode->discount_type === 'free') {
                $isFreeFlow     = true;
                $discountAmount = 35.00;
                $finalAmount    = 0.00;
            } elseif ($resolvedCode->discount_type === 'fixed_dollar_off') {
                $discountAmount = min((float) $resolvedCode->discount_value, 35.00);
                $finalAmount    = round(35.00 - $discountAmount, 2);
            } elseif ($resolvedCode->discount_type === 'percent_off') {
                $discountAmount = round(35.00 * ((float) $resolvedCode->discount_value / 100), 2);
                $finalAmount    = round(35.00 - $discountAmount, 2);
            }
        }

        if (!$isFreeFlow) {
            $request->validate([
                'card_number'    => ['required', 'regex:/^\d{13,19}$/'],
                'card_exp_month' => 'required|digits:2',
                'card_exp_year'  => 'required|digits:2',
                'card_cvc'       => ['required', 'regex:/^\d{3,4}$/'],
                'payment_amount' => 'required|numeric|min:0.01',
            ], [
                'card_number.regex'     => 'Please enter a valid card number.',
                'card_exp_month.digits' => 'Expiration month must be 2 digits.',
                'card_exp_year.digits'  => 'Expiration year must be 2 digits.',
                'card_cvc.regex'        => 'Please enter a valid CVC.',
            ]);

            $chargeAmount = $resolvedCode ? $finalAmount : (float) $request->payment_amount;

            $paymentSuccess = app(\App\Services\AuthorizeNetService::class)
                ->chargeOneTime(
                    $request->card_number,
                    $request->card_exp_month,
                    $request->card_exp_year,
                    $request->card_cvc,
                    $chargeAmount
                );

            if (!$paymentSuccess['success']) {
                return back()
                    ->withErrors(['payment' => 'Payment failed: ' . $paymentSuccess['message']])
                    ->withInput();
            }
        }

            $patient->first_name = $request->first_name;
            $patient->last_name = $request->last_name;
            $patient->date_of_birth = $request->date_of_birth;
            $patient->email = $request->email;
            $patient->street_address = $request->street_address;
            $patient->artist_id = $request->artist_id ?? NULL;
            $patient->artist_name = $request->artist_name ?? NULL;
            $patient->name_of_shop = $request->name_of_shop ?? NULL;
            $patient->city = $request->city;
            $patient->state = $request->state;
            $patient->zip = $request->zip;

            $driversLicensePath = null;
            $selfiePath = null;

            // Save the user data
            $patient->save();

            if ($request->hasFile('drivers_license_image')) {
                try {
                    $path = $request->file('drivers_license_image')
                        ->store("uploads/{$patient->id}/drivers_license", 'public');
                    $driversLicensePath = $path ?: null;
                    if (!$driversLicensePath) {
                        Log::error("Driver's license upload returned false for patient {$patient->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Driver's license upload failed for patient {$patient->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("No driver's license file received for patient {$patient->id}");
            }

            if ($request->hasFile('selfie_image')) {
                try {
                    $path = $request->file('selfie_image')
                        ->store("uploads/{$patient->id}/selfie", 'public');
                    $selfiePath = $path ?: null;
                    if (!$selfiePath) {
                        Log::error("Selfie upload returned false for patient {$patient->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Selfie upload failed for patient {$patient->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("No selfie file received for patient {$patient->id}");
            }

            $patient->update([
                'drivers_license' => $driversLicensePath,
                'patient_photo' => $selfiePath,
            ]);

            $answers = new PatientsCQI();
            $answers->status = 0; //not approved
            $answers->patient_id = $patient->id;
            $answers->artist_id = $request->artist_id ?? null;
            $answers->lidocaine = $request->lidocaine;
            $answers->bactine = $request->bactine;
            $answers->broken_skin = $request->broken_skin;
            $answers->eczema = $request->eczema;
            $answers->heart_rhythm = $request->heart_rhythm;
            $answers->liver_disease = $request->liver_disease;
            $answers->seizures = $request->seizures;
            $answers->pregnant = $request->pregnant;
            $answers->antiarrhythmic = $request->antiarrhythmic;
            $answers->seizure_meds = $request->seizure_meds;
            $answers->fainted = $request->fainted;
            $answers->methemoglobinemia = $request->methemoglobinemia;
            $answers->save();

            // If Didit webhook has not already set verification_method, the patient used manual upload
            if (!$patient->verification_method) {
                $patient->verification_method = 'manual_fallback';
                $patient->save();
            }

            // If this patient acknowledged the medical warning, generate a flagged submission PDF
            $acknowledgement = PatientAcknowledgement::where('patient_id', $patient->id)->latest()->first();
            if ($acknowledgement) {
                $questionLabels = [
                    'lidocaine'         => 'Q1: Allergic reaction to numbing creams or local anesthetics',
                    'bactine'           => 'Q2: Allergic reaction to Bactine or topical antiseptics',
                    'broken_skin'       => 'Q3: Broken skin or open wounds at treatment area',
                    'eczema'            => 'Q4: Severe eczema, psoriasis, or skin conditions at treatment area',
                    'heart_rhythm'      => 'Q5: Heart rhythm problems or arrhythmias',
                    'liver_disease'     => 'Q6: Severe liver disease',
                    'seizures'          => 'Q7: Seizures related to medications or anesthetics',
                    'pregnant'          => 'Q8: Currently pregnant or breastfeeding',
                    'antiarrhythmic'    => 'Q9: Medications for irregular heartbeat',
                    'seizure_meds'      => 'Q10: Medications for seizures or nerve pain',
                    'fainted'           => 'Q11: Fainted or severe reaction to local anesthetics',
                    'methemoglobinemia' => 'Q12: Methemoglobinemia or blood oxygen disorder',
                ];
                try {
                    $pdf = Pdf::loadView('pdf/flagged_submission', [
                        'patient'        => $patient,
                        'answers'        => $answers,
                        'acknowledgement' => $acknowledgement,
                        'questionLabels' => $questionLabels,
                    ]);
                    $filename = 'flagged_' . $patient->id . '_' . time() . '.pdf';
                    Storage::put('flagged_submissions/' . $filename, $pdf->output());
                    $acknowledgement->update(['pdf_path' => $filename]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate flagged submission PDF for patient ' . $patient->id . ': ' . $e->getMessage());
                }
            }

            $formStart = FormStart::where('email', $patient->email)->first();
            if ($formStart) {
                $formStart->completed = true;
                $formStart->patient_id = $patient->id;
                $formStart->abandoned_at = null;
                $formStart->save();
            }

            // ── Record discount redemption + bump usage count ─────────────
            if ($resolvedCode) {
                try {
                    DB::transaction(function () use ($resolvedCode, $patient, $request, $discountAmount) {
                        $locked = DiscountCode::where('id', $resolvedCode->id)->lockForUpdate()->first();
                        if ($locked) {
                            $locked->usage_count = ($locked->usage_count ?? 0) + 1;
                            if ($locked->usage_cap > 0 && $locked->usage_count >= $locked->usage_cap) {
                                $locked->status = 'exhausted';
                            }
                            $locked->save();
                        }
                        DiscountRedemption::create([
                            'discount_code_id'        => $resolvedCode->id,
                            'patient_id'              => $patient->id,
                            'session_id'              => $request->input('utm_session_id'),
                            'attempt_outcome'         => 'success',
                            'discount_amount_applied' => round($discountAmount, 2),
                            'redeemed_at'             => now(),
                        ]);
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to record discount redemption for patient ' . $patient->id . ': ' . $e->getMessage());
                }
            }

            // ── Mark UTM visit completed ──────────────────────────────────
            $utmSessionId = trim((string) $request->input('utm_session_id', ''));
            if ($utmSessionId !== '') {
                try {
                    $visit = UtmVisit::where('session_id', $utmSessionId)->first();
                    if ($visit) {
                        $visit->completed     = true;
                        $visit->patient_id    = $patient->id;
                        $visit->last_touch_at = now();
                        $visit->save();
                    } else {
                        UtmVisit::create([
                            'session_id'     => $utmSessionId,
                            'first_touch_at' => now(),
                            'last_touch_at'  => now(),
                            'completed'      => true,
                            'patient_id'     => $patient->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update UTM visit for patient ' . $patient->id . ': ' . $e->getMessage());
                }
            }

            $emailmessage = "New Submission:" . $patient->first_name . ' ' . $patient->last_name;

            \App\Jobs\SendAdminNotificationEmail::dispatch($emailmessage, $patient->id);

            return redirect('users/thank_you/');
    }

    public function show_cqi($patient_id)
    {
        $data['patient'] = Patients::where('id', $patient_id)->first();

        $data['states'] = MaintStates::get();

        return view('users/submited_cqi', $data);
    }

    public function thank_you()
    {
        return view('users/thank_you');
    }

    /**
     * Webhook callback for payment confirmation
     */
    public function callback(Request $request)
    {
        $payload = $request->input('payload', []);

        if (
            isset($payload['responseCode']) &&
            intval($payload['responseCode']) === 1
        ) {
            session(['registration_paid' => true]);
            return response()->json(['message' => 'Payment Successful']);
        }

        return response()->json(['message' => 'Payment Failed'], 422);
    }

    public function ajaxStartUser(Request $request)
    {

        $patient = new Patients();
        $patient->user_ip = $request->user_ip;
        $patient->terms_agree_check = $request->terms_agree_check;
        $patient->agree_time = date('Y-m-d H:i:s');
        $patient->email = '';
        $patient->save();

        return $patient->id;
    }

    public function trackFormStart(Request $request)
    {
        $email = $request->input('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response('OK', 200);
        }

        $existing = FormStart::where('email', $email)
            ->where('completed', false)
            ->first();

        if ($existing) {
            $existing->started_at = now();
            $existing->ip_address = $request->input('ip_address') ?: $request->ip();
            $existing->save();
        } else {
            FormStart::create([
                'email'      => $email,
                'ip_address' => $request->input('ip_address') ?: $request->ip(),
                'started_at' => now(),
                'completed'  => false,
            ]);
        }

        return response('OK', 200);
    }

    public function recordAcknowledgement(Request $request)
    {
        $patientId = $request->input('patient_id');
        $triggeredQuestions = $request->input('triggered_questions', []);

        if (!is_array($triggeredQuestions) || empty($triggeredQuestions)) {
            return response()->json(['recorded' => false, 'reason' => 'no triggered questions'], 422);
        }

        PatientAcknowledgement::create([
            'patient_id'          => $patientId ?: null,
            'session_id'          => session()->getId(),
            'ip_address'          => $request->ip(),
            'triggered_questions' => $triggeredQuestions,
            'acknowledged_at'     => now(),
        ]);

        return response()->json(['recorded' => true]);
    }
}
