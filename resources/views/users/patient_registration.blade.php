@extends('layouts.app')

@section('content')
<style>
    .step-indicator { max-width: 640px; margin: 0 auto; }
    .step-indicator .step { display: flex; flex-direction: column; align-items: center; flex: 0 0 auto; min-width: 90px; }
    .step-indicator .step-circle {
        width: 32px; height: 32px; border-radius: 50%;
        background: #e9ecef; color: #adb5bd;
        display: flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: 0.9rem; line-height: 1;
        transition: background-color 0.2s, color 0.2s;
    }
    .step-indicator .step-label {
        font-size: 0.8rem; color: #adb5bd; margin-top: 0.4rem;
        text-align: center; transition: color 0.2s;
    }
    .step-indicator .step-line {
        flex: 1 1 auto; height: 2px; background: #e9ecef;
        margin: 15px 0.5rem 0; transition: background-color 0.2s;
    }
    .step-indicator .step.active .step-circle { background: #1a9cd8; color: #fff; }
    .step-indicator .step.active .step-label { color: #1a9cd8; font-weight: 600; }
    .step-indicator .step.completed .step-circle { background: #adb5bd; color: #fff; }
    .step-indicator .step.completed .step-label { color: #6c757d; }
    .step-indicator .step-line.completed { background: #1a9cd8; }

    #scrollHint {
        position: fixed; left: 50%; bottom: 1rem; transform: translateX(-50%);
        z-index: 1500; pointer-events: none;
        background: rgba(255,255,255,0.9);
        color: #1a9cd8;
        padding: 0.4rem 0.9rem; border-radius: 999px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        font-size: 0.85rem; font-weight: 500;
        display: flex; align-items: center; gap: 0.4rem;
        opacity: 0; transition: opacity 0.3s ease;
    }
    #scrollHint.visible { opacity: 1; }
    #scrollHint .scroll-chevron {
        display: inline-block;
        animation: scrollHintBounce 1.4s ease-in-out infinite;
    }
    @keyframes scrollHintBounce {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(4px); }
    }
</style>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Medical Intake & Prescription Evaluation</h3>
        <a href="mailto:admin@studiomeds.com?subject=StudioMeds%20-%20Intake%20Form%20Help" class="btn btn-sm" id="contactHelpBtn" style="color:#1a9cd8;border-color:#1a9cd8;position:relative;">Having Trouble? Contact Us</a>
<span id="contactHelpFallback" style="display:none;position:absolute;right:1rem;top:3.5rem;z-index:9999;background:#fff;border:1px solid #1a9cd8;color:#333;padding:0.5rem 0.75rem;border-radius:6px;font-size:0.85rem;box-shadow:0 2px 8px rgba(0,0,0,0.12);white-space:nowrap;">
    Please email us at admin@studiomeds.com for assistance.
</span>
    </div>

    <form method="post" id="cqiForm" enctype="multipart/form-data" action="{{ url('users/store_patient') }}" novalidate>
        <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">
        <input type="hidden" name="user_ip" id="user_ip" value="{{ old('user_ip') }}">
        @csrf
        @method('post')

        <div class=" container py-4">

            <div class="card-body">
                <div id="stepIndicator" class="step-indicator d-flex align-items-start justify-content-between mb-4">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Demographics &amp; ID</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Medical Questions</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Payment</div>
                    </div>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif




                <div class="row g-3 p-2">
                    @if($artist != NULL)
                    <h3>Artist Information</h3>
                    <div class="col-md-6">
                        <h3>Artist Name</h3>
                        @if($artist->artist_name)
                        <h4>{{ $artist->artist_name }}</h4>
                        @else
                        <h4>{{ $artist->first_name }} {{ $artist->last_name }}</h4>
                        @endif
                        <input type="hidden" name="artist_id" value="{{ $artist->id }}">
                    </div>

                    <div class="col-md-6">
                        <h3>Artist Shop</h3>
                        <h4>{{ $artist->name_of_shop }}</h4>
                    </div>
                    @else
                    @endif
                </div>


                <div class="row g-3 p-2">
                    <h3>Your Information</h3>

                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                    </div>

                    <div class="col-md-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" autocomplete="bday" required>
                    </div>
                </div>

                {{-- ADDRESS --}}
                <div class="row g-3 p-2">
                    <h3>Mailing Address</h3>

                    <div class="col-md-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" class="form-control" name="street_address" value="{{ old('street_address') }}" autocomplete="street-address" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" value="{{ old('city') }}" autocomplete="address-level2" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <select name="state" id="state_value" class="form-select" required>
                            <option value="">Select State</option>
                            <option value="Alabama" {{ old('state') === 'Alabama' ? 'selected' : '' }}>Alabama</option>
                            <option value="Alaska" {{ old('state') === 'Alaska' ? 'selected' : '' }}>Alaska</option>
                            <option value="Arizona" {{ old('state') === 'Arizona' ? 'selected' : '' }}>Arizona</option>
                            <option value="Arkansas" {{ old('state') === 'Arkansas' ? 'selected' : '' }}>Arkansas</option>
                            <option value="California" {{ old('state') === 'California' ? 'selected' : '' }}>California</option>
                            <option value="Colorado" {{ old('state') === 'Colorado' ? 'selected' : '' }}>Colorado</option>
                            <option value="Connecticut" {{ old('state') === 'Connecticut' ? 'selected' : '' }}>Connecticut</option>
                            <option value="Delaware" {{ old('state') === 'Delaware' ? 'selected' : '' }}>Delaware</option>
                            <option value="District of Columbia" {{ old('state') === 'District of Columbia' ? 'selected' : '' }}>District of Columbia</option>
                            <option value="Florida" {{ old('state') === 'Florida' ? 'selected' : '' }}>Florida</option>
                            <option value="Georgia" {{ old('state') === 'Georgia' ? 'selected' : '' }}>Georgia</option>
                            <option value="Hawaii" {{ old('state') === 'Hawaii' ? 'selected' : '' }}>Hawaii</option>
                            <option value="Idaho" {{ old('state') === 'Idaho' ? 'selected' : '' }}>Idaho</option>
                            <option value="Illinois" {{ old('state') === 'Illinois' ? 'selected' : '' }}>Illinois</option>
                            <option value="Indiana" {{ old('state') === 'Indiana' ? 'selected' : '' }}>Indiana</option>
                            <option value="Iowa" {{ old('state') === 'Iowa' ? 'selected' : '' }}>Iowa</option>
                            <option value="Kansas" {{ old('state') === 'Kansas' ? 'selected' : '' }}>Kansas</option>
                            <option value="Kentucky" {{ old('state') === 'Kentucky' ? 'selected' : '' }}>Kentucky</option>
                            <option value="Louisiana" {{ old('state') === 'Louisiana' ? 'selected' : '' }}>Louisiana</option>
                            <option value="Maine" {{ old('state') === 'Maine' ? 'selected' : '' }}>Maine</option>
                            <option value="Maryland" {{ old('state') === 'Maryland' ? 'selected' : '' }}>Maryland</option>
                            <option value="Massachusetts" {{ old('state') === 'Massachusetts' ? 'selected' : '' }}>Massachusetts</option>
                            <option value="Michigan" {{ old('state') === 'Michigan' ? 'selected' : '' }}>Michigan</option>
                            <option value="Minnesota" {{ old('state') === 'Minnesota' ? 'selected' : '' }}>Minnesota</option>
                            <option value="Mississippi" {{ old('state') === 'Mississippi' ? 'selected' : '' }}>Mississippi</option>
                            <option value="Missouri" {{ old('state') === 'Missouri' ? 'selected' : '' }}>Missouri</option>
                            <option value="Montana" {{ old('state') === 'Montana' ? 'selected' : '' }}>Montana</option>
                            <option value="Nebraska" {{ old('state') === 'Nebraska' ? 'selected' : '' }}>Nebraska</option>
                            <option value="Nevada" {{ old('state') === 'Nevada' ? 'selected' : '' }}>Nevada</option>
                            <option value="New Hampshire" {{ old('state') === 'New Hampshire' ? 'selected' : '' }}>New Hampshire</option>
                            <option value="New Jersey" {{ old('state') === 'New Jersey' ? 'selected' : '' }}>New Jersey</option>
                            <option value="New Mexico" {{ old('state') === 'New Mexico' ? 'selected' : '' }}>New Mexico</option>
                            <option value="New York" {{ old('state') === 'New York' ? 'selected' : '' }}>New York</option>
                            <option value="North Carolina" {{ old('state') === 'North Carolina' ? 'selected' : '' }}>North Carolina</option>
                            <option value="North Dakota" {{ old('state') === 'North Dakota' ? 'selected' : '' }}>North Dakota</option>
                            <option value="Ohio" {{ old('state') === 'Ohio' ? 'selected' : '' }}>Ohio</option>
                            <option value="Oklahoma" {{ old('state') === 'Oklahoma' ? 'selected' : '' }}>Oklahoma</option>
                            <option value="Oregon" {{ old('state') === 'Oregon' ? 'selected' : '' }}>Oregon</option>
                            <option value="Pennsylvania" {{ old('state') === 'Pennsylvania' ? 'selected' : '' }}>Pennsylvania</option>
                            <option value="Rhode Island" {{ old('state') === 'Rhode Island' ? 'selected' : '' }}>Rhode Island</option>
                            <option value="South Carolina" {{ old('state') === 'South Carolina' ? 'selected' : '' }}>South Carolina</option>
                            <option value="South Dakota" {{ old('state') === 'South Dakota' ? 'selected' : '' }}>South Dakota</option>
                            <option value="Tennessee" {{ old('state') === 'Tennessee' ? 'selected' : '' }}>Tennessee</option>
                            <option value="Texas" {{ old('state') === 'Texas' ? 'selected' : '' }}>Texas</option>
                            <option value="Utah" {{ old('state') === 'Utah' ? 'selected' : '' }}>Utah</option>
                            <option value="Vermont" {{ old('state') === 'Vermont' ? 'selected' : '' }}>Vermont</option>
                            <option value="Virginia" {{ old('state') === 'Virginia' ? 'selected' : '' }}>Virginia</option>
                            <option value="Washington" {{ old('state') === 'Washington' ? 'selected' : '' }}>Washington</option>
                            <option value="West Virginia" {{ old('state') === 'West Virginia' ? 'selected' : '' }}>West Virginia</option>
                            <option value="Wisconsin" {{ old('state') === 'Wisconsin' ? 'selected' : '' }}>Wisconsin</option>
                            <option value="Wyoming" {{ old('state') === 'Wyoming' ? 'selected' : '' }}>Wyoming</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ZIP</label>
                        <input type="text" class="form-control" name="zip" inputmode="numeric" value="{{ old('zip') }}" autocomplete="postal-code" pattern="\d{5}(-\d{4})?" maxlength="10" placeholder="12345" required>
                    </div>
                </div>

                {{-- IDENTITY VERIFICATION --}}
                <div class="row g-3 p-2" id="verification-section">
                    <h3>Identity Verification</h3>
                    <div class="col-12">
                        <p class="text-muted mb-2">We need to verify your identity before you continue. Click below to complete a quick ID check.</p>
                        <button type="button" class="btn btn-primary" id="didit-verify-btn" disabled>Verify My Identity</button>
                    </div>

                    {{-- Manual fallback uploads (hidden until Didit fails twice or AJAX error) --}}
                    <div id="manual-fallback-section" style="display:none;">
                        <div class="alert alert-info border border-info mt-3 p-4" style="border-width:2px !important;">
                            <h5 class="alert-heading mb-1">&#128274; Please Complete Identity Verification Below</h5>
                            <p class="mb-0">To continue, upload a photo of your government-issued ID and a selfie. Both are required before you can proceed.</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Driver’s License</label>
                                <small class="text-muted d-block mb-1">Take a photo or upload from your gallery.</small>
                                <input type="file" name="drivers_license_image" id="drivers_license_image"
                                       class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Selfie</label>
                                <small class="text-muted d-block mb-1">Take a selfie or upload a photo of yourself.</small>
                                <input type="file" name="selfie_image" id="selfie_image"
                                       class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="didit_verified" id="didit_verified" value="0">
                </div>

                {{-- Didit iframe modal overlay --}}
                <div id="didit-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:10000;align-items:center;justify-content:center;">
                    <div style="background:#fff;border-radius:8px;width:90%;max-width:520px;height:80vh;display:flex;flex-direction:column;overflow:hidden;position:relative;">
                        <div style="padding:12px 16px;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center;">
                            <strong>Identity Verification</strong>
                            <button type="button" id="didit-modal-close" style="background:none;border:none;font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
                        </div>
                        <iframe id="didit-iframe"
                                src=""
                                style="flex:1;border:none;width:100%;"
                                allow="camera; microphone; fullscreen; autoplay; encrypted-media">
                        </iframe>
                    </div>
                </div>

                {{-- Everything below verification is hidden until verification is complete --}}
                <div id="post-verification-section" style="display:none;">

                <h3>Medical Screening Questions</h3>

                <div class="py-2">
<div class="py-2 mb-3">
                        <label class=”form-label”>1. Have you ever had an allergic reaction to local anesthetics (lidocaine, benzocaine, or similar)?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="lidocaine" id="lidocaine_yes" value="1" {{ old('lidocaine') === '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="lidocaine _yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="lidocaine" id="lidocaine_no" value="0" {{ old('lidocaine') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="lidocaine _no">No</label>
                            </div>
                            @error('lidocaine') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">2. Have you ever had an allergic reaction to Bactine or topical antiseptics?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="bactine" id="bactine_yes" value="1" {{ old('bactine') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bactine_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="bactine" id="bactine_no" value="0" {{ old('bactine') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bactine_no">No</label>
                            </div>
                        </div>
                    </div>

<div class="mb-3">
                        <label class="form-label">3. Do you currently have broken skin, open wounds, or skin infections where the topical will be applied?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="broken_skin" id="broken_skin_yes" value="1" {{ old('broken_skin') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="broken_skin_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="broken_skin" id="broken_skin_no" value="0" {{ old('broken_skin') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="broken_skin_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">4. Do you have severe eczema, psoriasis, or other skin conditions where the topical will be applied?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="eczema" id="eczema_yes" value="1" {{ old('eczema') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="eczema_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="eczema" id="eczema_no" value="0" {{ old('eczema') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="eczema_no">No</label>
                            </div>
                        </div>
                    </div>

<div class="mb-3">
                        <label class="form-label">5. Do you have a serious heart rhythm condition such as heart block, Wolff-Parkinson-White, or ventricular tachycardia? Occasional skipped beats or palpitations do not count.</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="heart_rhythm" id="heart_rhythm_yes" value="1" {{ old('heart_rhythm') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="heart_rhythm_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="heart_rhythm" id="heart_rhythm_no" value="0" {{ old('heart_rhythm') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="heart_rhythm_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">6. Do you have severe liver disease?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="liver_disease" id="liver_disease_yes" value="1" {{ old('liver_disease') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="liver_disease_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="liver_disease" id="liver_disease_no" value="0" {{ old('liver_disease') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="liver_disease_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">7. Have you ever had seizures caused by medications or anesthetics?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="seizures" id="seizures_yes" value="1" {{ old('seizures') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="seizures_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="seizures" id="seizures_no" value="0" {{ old('seizures') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="seizures_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">8. Are you currently pregnant or breastfeeding?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="pregnant" id="pregnant_yes" value="1" {{ old('pregnant') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="pregnant_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="pregnant" id="pregnant_no" value="0" {{ old('pregnant') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="pregnant_no">No</label>
                            </div>
                        </div>
                    </div>

<div class="mb-3">
                        <label class="form-label">9. Are you taking prescription antiarrhythmic medications for a heart rhythm disorder (amiodarone, mexiletine, flecainide, or quinidine)? Blood pressure medications and beta blockers do not count.</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="antiarrhythmic" id="antiarrhythmic_yes" value="1" {{ old('antiarrhythmic') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="antiarrhythmic_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="antiarrhythmic" id="antiarrhythmic_no" value="0" {{ old('antiarrhythmic') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="antiarrhythmic_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">10. Are you taking medications for seizures or nerve pain that your provider has warned may interact with anesthetics?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="seizure_meds" id="seizure_meds_yes" value="1" {{ old('seizure_meds') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="seizure_meds_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="seizure_meds" id="seizure_meds_no" value="0" {{ old('seizure_meds') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="seizure_meds_no">No</label>
                            </div>
                        </div>
                    </div>

<div class="mb-3">
                        <label class="form-label">11. Have you ever had a severe reaction to local anesthetics (hives, difficulty breathing, seizures, or loss of consciousness)? Exclude normal nervousness or mild dizziness.</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="fainted" id="fainted_yes" value="1" {{ old('fainted') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="fainted_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="fainted" id="fainted_no" value="0" {{ old('fainted') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="fainted_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">12. Have you ever been diagnosed with methemoglobinemia or a blood disorder affecting oxygen carrying capacity?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="methemoglobinemia" id="methemoglobinemia_yes" value="1" {{ old('methemoglobinemia') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="methemoglobinemia_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input q-radio" type="radio" name="methemoglobinemia" id="methemoglobinemia_no" value="0" {{ old('methemoglobinemia') === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="methemoglobinemia_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>

                </div>{{-- end post-verification-section --}}
            </div>

        </div>

        <div class="card-footer text-start" id="submit-footer" style="display:none;">
            <button type="submit" class="btn btn-primary btn-lg submit" id="submitBtn">
                <i class="fas fa-save"></i> Submit
            </button>
        </div>

    </form>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Terms & Conditions</h3>
            </div>
            <div class="modal-body" style="max-height:500px; overflow-y:scroll; ">
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Service Summary</h3>
                        <p><strong>Virtual Medical Evaluation</strong></p>
                        <p>You are purchasing a professional virtual medical evaluation performed by a licensed physician. If clinically appropriate, a prescription may be issued. StudioMeds, PLLC does not sell, dispense, or ship medication.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Price & Billing Disclosure</h3>
                        <p><strong>Price: $35.00 (one-time fee)</strong></p>
                        <p>No subscriptions. No recurring charges.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Refund Policy Summary</h3>
                        <ul>
                            <li>You will <strong>not be charged</strong> if you are deemed <strong>not appropriate for a virtual medical assessment for over the counter topical anesthetic prescription.</strong></li>
                            <li>Once your evaluation is completed and a prescription is issued, <strong>no refunds are provided once the clinic intake questionnaire has been submitted.</strong></li>
                        </ul>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Terms & Conditions</h3>
                        <p><i>You are purchasing a <strong>virtual medical evaluation</strong>, not a medication or product. If clinically appropriate, a prescription may be issued at the physician’s discretion; approval is not guaranteed. StudioMeds does not sell or dispense medication and is not a pharmacy.</i></p>
                        <p><i>You will <strong>not be charged</strong> if you are not appropriate for a virtual medical assessment. Once your evaluation is completed and a prescription is issued, the service is fully rendered and <strong>no refunds are provided</strong>.</i></p>
                        <p><i>By proceeding, you consent to electronic communications and agree that disputes are resolved through binding arbitration under Michigan law.</i></p>
                        <p><a href="{{ url('/pdfs/TERMS AND CONDITIONS.pdf') }}" target="_blank" rel="noopener"> View full Terms & Conditions</a></p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Privacy Policy & Electronic Communication Consent</h3>
                        <p>StudioMeds collects personal, health, and technical information you provide in order to conduct your virtual medical evaluation, determine eligibility for a prescription, and deliver service-related communications, including prescription delivery by email.</p>
                        <p>Your information is <strong>not sold</strong> and is shared only with licensed physicians, required service providers, payment processors, and regulatory or legal authorities when necessary. StudioMeds uses reasonable safeguards to protect your information, though no system is completely secure.</p>
                        <p>By proceeding, you consent to the collection, use, and secure handling of your information as described in the Privacy Policy.</p>
                        <p><a href="{{ url('/pdfs/PRIVACY POLICY.pdf') }}" target="_blank" rel="noopener"> View full Privacy Policy</a></p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Patient Consent for Clinical Intake and Treatment</h3>
                        <p>You are completing a clinical intake questionnaire to allow a licensed provider to determine whether it is medically appropriate to prescribe an over-the-counter topical anesthetic. Approval is not guaranteed, and the provider is evaluating eligibility for the medication only and is not performing your procedure.</p>
                        <p>You understand that these medications must be used only as prescribed and that improper use—including excessive dosing, combining products, prolonged application, or use on compromised skin—may result in serious injury or death. You agree to follow all instructions and accept responsibility for ensuring proper use.</p>
                        <p>You acknowledge that use of these medications for body art or cosmetic procedures is off-label and not FDA-approved for this purpose, and that such use carries known and potential risks. By proceeding, you voluntarily consent to the clinical evaluation and treatment described.</p>
                        <p><a href="{{ url('/pdfs/Patient Consent.pdf') }}" target="_blank" rel="noopener"> View full Patient Consent for Clinical Intake and Treatment</a></p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <h3>Acknowledgment</h3>
                        <p>I acknowledge that I am purchasing a virtual medical evaluation, not a medication or product, and that a prescription may be issued only if clinically appropriate. I have read and agree to the Terms & Conditions, including the Refund Policy, the Privacy Policy, and the Patient Consent for Clinical Intake and Treatment. I understand that I will not be charged if I am not appropriate for a virtual medical assessment, and that once my clinical intake questionnaire is submitted and a prescription is issued, no refunds are provided. I consent to the collection, use, and secure handling of my information and to receiving service-related electronic communications, including prescription delivery.</p>
                        <p><a href="{{ url('/pdfs/TERMS AND CONDITIONS.pdf') }}" target="_blank" rel="noopener"> Terms & Conditions</a></br>
                            <a href="{{ url('/pdfs/PRIVACY POLICY.pdf') }}" target="_blank" rel="noopener"> Privacy Policy</a></br>
                            <a href="{{ url('/pdfs/Patient Consent.pdf') }}" target="_blank" rel="noopener"> Patient Consent for Clinical Intake and Treatment</a>
                        </p>
                        <label><input type="checkbox" name="terms_agree_check" id="terms_agree_check" value="1"> <strong>I Acknowledge and Agree — Terms, Privacy & Medical Consent</strong></label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="terms_agree" disabled>
                    Get Started
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="medicalWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Medical History Notice</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5">Given your medical history it is recommended you see an in person provider to receive a prescription for topical anesthetics.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger px-5" id="medicalWarningAcknowledge">I Understand</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Payment Required</h5>
            </div>

            <div class="modal-body">

                <p>A one-time <strong>$35.00</strong> payment is required.</p>

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label>Card Number</label>
                        <input type="text" id="modal_card_number" name="modal_card_number" class="form-control"
                               inputmode="numeric" placeholder="Card number" value="{{ old('modal_card_number') }}">
                    </div>

                    <div class="col-4 col-md-2">
                        <label>Exp (MM)</label>
                        <input type="text" id="modal_exp_month" name="modal_exp_month" class="form-control"
                               maxlength="2" placeholder="MM" inputmode="numeric" value="{{ old('modal_exp_month') }}">
                    </div>

                    <div class="col-4 col-md-2">
                        <label>Exp (YY)</label>
                        <input type="text" id="modal_exp_year" name="modal_exp_year" class="form-control"
                               maxlength="2" placeholder="YY" inputmode="numeric" value="{{ old('modal_exp_year') }}">
                    </div>

                    <div class="col-4 col-md-2">
                        <label>CVC</label>
                        <input type="text" id="modal_cvc" name="modal_cvc" class="form-control"
                               inputmode="numeric" maxlength="4" placeholder="CVC" value="{{ old('modal_cvc') }}">
                    </div>

                    <input type="hidden" id="modal_payment_amount" value="35.00">
                </div>
                <div class="mt-2 d-flex align-items-center gap-2">
                    <img src="{{ asset('images/cards/visa.svg') }}" height="28" alt="Visa">
                    <img src="{{ asset('images/cards/mastercard.svg') }}" height="28" alt="Mastercard">
                    <img src="{{ asset('images/cards/americanexpress.svg') }}" height="28" alt="American Express">
                    <img src="{{ asset('images/cards/discover.svg') }}" height="28" alt="Discover">
                </div>

                <div id="paymentProcessing" class="text-center mt-3" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2"><strong>Processing payment, please wait...</strong></p>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <button class="btn btn-primary" id="confirmPaymentBtn">
                    Pay & Submit
                </button>
            </div>

        </div>
    </div>
</div>

<div id="scrollHint" aria-hidden="true">
    <span>Scroll down to get started</span>
    <span class="scroll-chevron">&#x25BC;</span>
</div>

@endsection



@section('script')
<script>
    $(document).ready(function() {
        // Scroll-down hint — visible only if the page loads at the top
        (function() {
            var $hint = $('#scrollHint');
            if (!$hint.length) { return; }
            if ((window.scrollY || window.pageYOffset || 0) > 5) { return; }
            $hint.addClass('visible');
            var hide = function() {
                $hint.removeClass('visible');
                window.removeEventListener('scroll', onScroll);
                setTimeout(function() { $hint.remove(); }, 400);
            };
            var onScroll = function() {
                if ((window.scrollY || window.pageYOffset || 0) > 5) { hide(); }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
        })();

        // Step indicator helper — purely visual, no impact on form logic
        function setStep(n) {
            $('#stepIndicator .step').each(function() {
                var step = parseInt($(this).attr('data-step'), 10);
                $(this).removeClass('active completed');
                var $circle = $(this).find('.step-circle');
                if (step < n) {
                    $(this).addClass('completed');
                    $circle.html('&#10003;');
                } else if (step === n) {
                    $(this).addClass('active');
                    $circle.text(step);
                } else {
                    $circle.text(step);
                }
            });
            $('#stepIndicator .step-line').each(function(i) {
                if (i < n - 1) { $(this).addClass('completed'); }
                else { $(this).removeClass('completed'); }
            });
        }
        window.__setStep = setStep;

        $('#paymentModal').on('show.bs.modal', function() { setStep(3); });

        $('#contactHelpBtn').on('click', function() {
            var emailOpened = false;
            $(window).one('blur', function() { emailOpened = true; });
            setTimeout(function() {
                if (!emailOpened) {
                    $('#contactHelpFallback').fadeIn(200);
                    $(document).one('click', function(e) {
                        if (!$(e.target).closest('#contactHelpBtn, #contactHelpFallback').length) {
                            $('#contactHelpFallback').fadeOut(200);
                        }
                    });
                }
            }, 500);
        });


        @if(!$errors->any())
            @if(!old('patient_id'))
            const termsModal = new bootstrap.Modal(
                document.getElementById('termsModal')
            );
            termsModal.show();
            @endif
        @endif

        fetch('https://api.ipify.org?format=json')
            .then(response => response.json())
            .then(data => {
                document.getElementById('user_ip').value = data.ip;
            });

        $('#email').on('blur', function() {
            var email = $(this).val().trim();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                return;
            }
            $.ajax({
                url: '/ajax/track-form-start',
                type: 'POST',
                dataType: 'json',
                data: {
                    email: email,
                    ip_address: $('#user_ip').val()
                }
            });
        });

        $('#terms_agree_check').on('change', function() {

            if ($(this).is(':checked')) {
                $('#terms_agree').prop('disabled', false);
            } else {
                $('#terms_agree').prop('disabled', true);
            }
        });

        $('#terms_agree').on('click', function() {

            var user_ip = jQuery('#user_ip').val();
            var terms_agree_check = jQuery('#terms_agree_check').val();

            jQuery.ajax({
                url: "/ajaxStartUser",
                type: 'GET',
                dataType: 'json',
                data: {
                    user_ip: user_ip,
                    terms_agree_check: terms_agree_check
                },
                success: function(result) {

                    $("#patient_id").val(result);

                    termsModal.hide();

                }
            });
        });


        const medicalFields = ['lidocaine','bactine','broken_skin','eczema','heart_rhythm','liver_disease','seizures','pregnant','antiarrhythmic','seizure_meds','fainted','methemoglobinemia'];

        function anyYesSelected() {
            return medicalFields.some(function(name) {
                return $('input[name="' + name + '"]:checked').val() === '1';
            });
        }

        function allQuestionsAnswered() {
            return medicalFields.every(function(name) {
                return $('input[name="' + name + '"]:checked').length > 0;
            });
        }

        // Prevent the year portion of the date input from exceeding 4 digits
        $('#date_of_birth').on('change', function() {
            const val = $(this).val(); // "YYYY-MM-DD"
            if (!val) return;
            const parts = val.split('-');
            if (parts[0] && parts[0].length > 4) {
                parts[0] = parts[0].slice(0, 4);
                $(this).val(parts.join('-'));
            }
        });

        function calculateAge(dob) {
            if (!dob)
                return null;
            const birth = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate()))
                age--;
            return age;
        }

        // Clear red border from the answered question's container
        $('.q-radio').on('change', function() {
            $(this).closest('.mb-3, .py-2').css({ 'outline': '', 'border-radius': '', 'padding': '' });
        });

        // ── Didit verify button gating ──────────────────────────────────────────
        function checkVerifyBtnReady() {
            var allFilled =
                $('input[name="first_name"]').val().trim() !== '' &&
                $('input[name="last_name"]').val().trim() !== '' &&
                $('input[name="email"]').val().trim() !== '' &&
                $('input[name="date_of_birth"]').val().trim() !== '' &&
                $('input[name="street_address"]').val().trim() !== '' &&
                $('input[name="city"]').val().trim() !== '' &&
                $('input[name="zip"]').val().trim() !== '' &&
                $('#state_value').val().trim() !== '';
            $('#didit-verify-btn').prop('disabled', !allFilled);
        }

        // Attach listeners to every field
        $('input[name="first_name"], input[name="last_name"], input[name="email"], ' +
          'input[name="date_of_birth"], input[name="street_address"], ' +
          'input[name="city"], input[name="zip"]').on('input change', checkVerifyBtnReady);

        // State is now a native select — fires change events directly
        $('#state_value').on('change', checkVerifyBtnReady);
        // ── End Didit verify button gating ──────────────────────────────────────

        // ── Didit verification ──────────────────────────────────────────────────
        var diditFailureCount = 0;

        function showPostVerification() {
            $('#post-verification-section').show();
            $('#submit-footer').show();
            if (typeof setStep === 'function') { setStep(2); }
        }

        function showManualFallback() {
            $('#didit-verify-btn').hide();
            $('#manual-fallback-section').show();
            $('#drivers_license_image').prop('required', true);
            $('#selfie_image').prop('required', true);
            showPostVerification();
        }

        var diditPollInterval = null;
        var diditPollStart    = null;
        var diditActive       = false; // true only while the modal is open and polling
        var DIDIT_POLL_MS     = 3000;
        var DIDIT_TIMEOUT_MS  = 120000; // 2 minutes

        function stopPolling() {
            if (diditPollInterval) {
                clearInterval(diditPollInterval);
                diditPollInterval = null;
            }
        }

        // closeDiditModal: closes the modal and stops polling.
        // Does NOT show post-verification — call showPostVerification() explicitly after
        // this only when verification actually succeeded.
        function closeDiditModal() {
            diditActive = false;
            stopPolling();
            $('#didit-modal-overlay').css('display', 'none');
            $('#didit-iframe').attr('src', '');
        }

        function startPolling(patientId) {
            diditActive    = true;
            diditPollStart = Date.now();
            diditPollInterval = setInterval(function() {
                // If modal was closed manually, diditActive is false — stop and do nothing
                if (!diditActive) {
                    stopPolling();
                    return;
                }
                if (Date.now() - diditPollStart >= DIDIT_TIMEOUT_MS) {
                    closeDiditModal();
                    showManualFallback();
                    return;
                }
                $.ajax({
                    url: '/ajax/didit-status',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        patient_id: patientId
                    },
                    success: function(resp) {
                        // Guard against in-flight responses arriving after X was clicked
                        if (!diditActive) { return; }
                        if (resp.verified) {
                            closeDiditModal();
                            $('#didit_verified').val('1');
                            $('#didit-verify-btn').hide();
                            showPostVerification();
                        }
                    }
                });
            }, DIDIT_POLL_MS);
        }

        $('#didit-verify-btn').on('click', function() {
            var patientId = $('#patient_id').val();
            if (!patientId) {
                alert('Please agree to the terms first to start your session.');
                return;
            }
            $.ajax({
                url: '/ajax/didit-session',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    patient_id: patientId
                },
                success: function(resp) {
                    if (resp.verification_url) {
                        $('#didit-iframe').attr('src', resp.verification_url);
                        $('#didit-modal-overlay').css('display', 'flex');
                        startPolling(patientId);
                    } else {
                        showManualFallback();
                    }
                },
                error: function() {
                    showManualFallback();
                }
            });
        });

        $('#didit-modal-close').on('click', function() {
            closeDiditModal();
            showManualFallback();
        });
        // ── End Didit verification ───────────────────────────────────────────────

        // Validate on submit — only preventDefault when something actually fails
        var paymentReady = false;

        $('#cqiForm').on('submit', function(e) {

            // If payment has been confirmed, let the form post through normally
            if (paymentReady) { return; }

            // 1. Age check
            $('#dob-age-error').remove();
            const dob = $('#date_of_birth').val();
            const age = calculateAge(dob);
            if (age !== null && age < 18) {
                e.preventDefault();
                $('#date_of_birth').after('<div id="dob-age-error" class="alert alert-danger mt-2">You must be 18 or older to submit this form.</div>');
                $('#date_of_birth')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // 2. All 12 medical questions must be answered
            $('#medical-incomplete-error').remove();
            if (!allQuestionsAnswered()) {
                e.preventDefault();
                const firstUnanswered = medicalFields.find(function(name) {
                    return $('input[name="' + name + '"]:checked').length === 0;
                });
                const target = $('input[name="' + firstUnanswered + '"]').closest('.mb-3, .py-2');
                target.css({ 'outline': '2px solid #dc3545', 'border-radius': '4px', 'padding': '8px' });
                $('<div id="medical-incomplete-error" class="alert alert-danger mt-2">Please answer all medical history questions before submitting.</div>')
                    .insertBefore(target);
                target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // 3. If any Yes — show medical warning modal, block submission
            if (anyYesSelected()) {
                e.preventDefault();
                $('#medicalWarningModal').modal('show');
                return;
            }

            // 4. All No — show payment modal
            e.preventDefault();
            $('#paymentModal').modal('show');
        });

        // "I Understand" closes warning modal and records the acknowledgement for audit
        $('#medicalWarningAcknowledge').on('click', function() {
            var triggeredQuestions = [];
            ['lidocaine','bactine','broken_skin','eczema','heart_rhythm','liver_disease','seizures','pregnant','antiarrhythmic','seizure_meds','fainted','methemoglobinemia'].forEach(function(name) {
                if ($('input[name="' + name + '"]:checked').val() === '1') {
                    triggeredQuestions.push(name);
                }
            });

            $.post('/ajax/record_acknowledgement', {
                _token:             $('meta[name="csrf-token"]').attr('content'),
                patient_id:         $('#patient_id').val(),
                triggered_questions: triggeredQuestions
            });

            $('#medicalWarningModal').modal('hide');
        });


        $('#modal_exp_month').on('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 2);
            if (this.value.length === 2) { $('#modal_exp_year').focus(); }
        });

        $('#modal_exp_year').on('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 2);
            if (this.value.length === 2) { $('#modal_cvc').focus(); }
        });

        $('#confirmPaymentBtn').on('click', function() {
            $('#confirmPaymentBtn').prop('disabled', true).text("Processing...");
            $('#paymentProcessing').show();

            $('<input type="hidden" name="card_number">').val($('#modal_card_number').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_exp_month">').val($('#modal_exp_month').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_exp_year">').val($('#modal_exp_year').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_cvc">').val($('#modal_cvc').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="payment_amount">').val($('#modal_payment_amount').val()).appendTo('#cqiForm');

            var siteKey = '{{ config("services.recaptcha.site_key") }}';

            function onError() {
                $('#confirmPaymentBtn').prop('disabled', false).text("Pay & Submit");
                $('#paymentProcessing').hide();
                alert('Something went wrong. Please try again.');
            }

            if (siteKey && typeof grecaptcha !== 'undefined') {
                grecaptcha.ready(function() {
                    grecaptcha.execute(siteKey, { action: 'submit_patient' })
                        .then(function(token) {
                            if ($('#recaptcha_token').length === 0) {
                                $('<input>').attr({ type: 'hidden', id: 'recaptcha_token', name: 'recaptcha_token', value: token }).appendTo('#cqiForm');
                            } else {
                                $('#recaptcha_token').val(token);
                            }
                            paymentReady = true;
                            $('#cqiForm').submit();
                        }).catch(function() { onError(); });
                });
            } else {
                paymentReady = true;
                $('#cqiForm').submit();
            }
        });
    });
</script>
@endsection