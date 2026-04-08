<?php

namespace App\Http\Controllers;

use App\Models\MaintStates;
use App\Models\User;
use App\Models\PatientsCQI;
use App\Models\Patients;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Services\AuthorizeNetService;
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

            if (isset($user)) {
                $user->delete();
            }

            return redirect()->back()->withErrors('Registration or subscription failed: ' . $e->getMessage());
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
            'date_of_birth' => 'required|date|before:today',
            'street_address' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email',
            'artist_name' => 'string|max:255',
            'name_of_shop' => 'string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required',
            'zip' => 'required|string|max:10',
            'drivers_license' => 'required|image|max:10240',
            'selfie_photo' => 'required|image|max:10240',
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

            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => config('services.recaptcha.secret_key'),
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]
            );

            $captchaData = $response->json();

            if (
                !$captchaData['success'] ||
                $captchaData['score'] < config('services.recaptcha.score_threshold') ||
                $captchaData['action'] !== 'submit_patient'
            ) {
                return back()->withErrors([
                    'captcha' => 'Suspicious activity detected. Please try again.'
                ])->withInput();
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

        $request->validate(array_merge([
            'state'         => ['required', 'string', \Illuminate\Validation\Rule::in($validStates)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()],
        ], $medicalValidation), [
            'state.in'                      => 'Please select a valid US state from the list.',
            'date_of_birth.before_or_equal' => 'You must be 18 or older to submit this form.',
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

        $request->validate([
            'card_number'    => ['required', 'regex:/^\d{13,19}$/'],
            'card_exp_month' => 'required|digits:2',
            'card_exp_year'  => 'required|digits:2',
            'card_cvc'       => ['required', 'regex:/^\d{3,4}$/'],
            'payment_amount' => 'required|numeric|min:0.01',
        ], [
            'card_number.regex'    => 'Please enter a valid card number.',
            'card_exp_month.digits'=> 'Expiration month must be 2 digits.',
            'card_exp_year.digits' => 'Expiration year must be 2 digits.',
            'card_cvc.regex'       => 'Please enter a valid CVC.',
        ]);

        $paymentSuccess = app(\App\Services\AuthorizeNetService::class)
            ->chargeOneTime(
                $request->card_number,
                $request->card_exp_month,
                $request->card_exp_year,
                $request->card_cvc,
                $request->payment_amount
            );

        if (!$paymentSuccess['success']) {

            return back()
                ->withErrors(['payment' => 'Payment failed: ' . $paymentSuccess['message']])
                ->withInput();
        } else {

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
                $driversLicensePath = $request->file('drivers_license_image')
                    ->store("uploads/{$patient->id}/drivers_license", 'public');
            }

            if ($request->hasFile('selfie_image')) {
                $selfiePath = $request->file('selfie_image')
                    ->store("uploads/{$patient->id}/selfie", 'public');
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

            $emailmessage = "New Submission:" . $patient->first_name . ' ' . $patient->last_name;

            // send
            Mail::raw($emailmessage, function ($m) {
                $m->to(config('services.admin.notification_email'))
                  ->bcc(config('services.admin.bcc_email'))
                  ->from(config('services.admin.from_email'))
                  ->subject('New Patient Submission');
            });
            return redirect('users/thank_you/');
        }
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
        Log::info("Authorize.net Callback", $request->all());

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
}
