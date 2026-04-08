@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Topical Anesthetic / CQI Screening</h3>
    </div>

    <form method="post" id="cqiForm" enctype="multipart/form-data" action="{{ url('users/store_patient') }}" autocomplete="off">
        <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">
        <input type="hidden" name="user_ip" id="user_ip" value="{{ old('user_ip') }}">
        @csrf
        @method('post')

        <div class=" container py-4">

            <div class="card-body">
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

                <div class="alert alert-warning" id="followUpHint" style="display:none;">
                    <strong>Important:</strong> Patient answered "Yes" — follow up with a medical provider.
                </div>


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
                    <h3>User Information</h3>

                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                    </div>
                </div>

                {{-- ADDRESS --}}
                <div class="row g-3 p-2">
                    <h3>Mailing Address</h3>

                    <div class="col-md-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" class="form-control" name="street_address" value="{{ old('street_address') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" value="{{ old('city') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" name="state"
                               list="states-list"
                               placeholder="Type a state..."
                               value="{{ old('state') }}"
                               autocomplete="off"
                               required>
                        <datalist id="states-list">
                            @foreach($states as $state)
                            <option value="{{ $state->full }}">{{ $state->abbreviation }}</option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ZIP</label>
                        <input type="text" class="form-control" name="zip" value="{{ old('zip') }}" required>
                    </div>
                </div>

                {{-- FILE UPLOADS --}}
                <div class="row g-3 p-2">
                    <div class="col">
                        <label class="form-label">Driver’s License</label>
                        <input type="file" name="drivers_license_image" class="form-control" required>
                    </div>

                    <div class="col">
                        <label class="form-label">Selfie</label>
                        <input type="file" name="selfie_image" class="form-control" required>
                    </div>
                </div>

                <div class="py-2">
                    <h5>Section 1: Allergies / Sensitivities</h5>
                    <div class="py-2 mb-3">
                        <label class="form-label">1. Have you ever had an allergic reaction to lidocaine or other “-caine” anesthetics (such as benzocaine, prilocaine, or tetracaine)?</label>
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
                        <label class="form-label">2. Have you ever had an allergic reaction to Bactine®, benzalkonium chloride, or other topical antiseptics?</label>
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

                    <h5>Section 2: Skin Conditions / Open Wounds</h5>
                    <div class="mb-3">
                        <label class="form-label">3. Do you currently have broken skin, open wounds, or skin infections at the area where the topical will be applied?</label>
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
                        <label class="form-label">4. Do you have a history of severe eczema, psoriasis, or other skin conditions at the area to be treated?</label>
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

                    <h5>Section 3: Medical History</h5>
                    <div class="mb-3">
                        <label class="form-label">5. Do you have any heart rhythm problems (arrhythmias) or heart block?</label>
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
                        <label class="form-label">7. Have you ever experienced seizures related to medications or anesthetics?</label>
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

                    <h5>Section 4: Medications / Substances</h5>
                    <div class="mb-3">
                        <label class="form-label">9. Are you currently taking any antiarrhythmic medications (such as amiodarone, mexiletine, quinidine)?</label>
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
                        <label class="form-label">10. Are you currently taking any medications for seizures or nerve pain that your provider has warned may interact with anesthetics?</label>
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

                    <h5>Section 5: Past Reactions / Complications</h5>
                    <div class="mb-3">
                        <label class="form-label">11. Have you ever fainted, felt dizzy, or had a severe reaction when using local anesthetics before?</label>
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
                        <label class="form-label">12. Have you ever been told you have a condition called methemoglobinemia or a blood disorder affecting oxygen carrying capacity?</label>
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
            </div>

        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary submit">
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
                        <label><input type="checkbox" name="terms_agree_check" id="terms_agree_check" value="1"> <strong>I Acknowledgment and Agree — Terms, Privacy & Medical Consent</strong></label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="terms_agree" disabled>
                    I Agree
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Payment Required</h5>
            </div>

            <div class="modal-body">

                <p>A one-time <strong>$35.00</strong> payment is required.</p>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label>Card Number</label>
                        <input type="text" id="modal_card_number" name="modal_card_number" class="form-control" value="{{ old('modal_card_number') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Exp (MM)</label>
                        <input type="text" id="modal_exp_month" name="modal_exp_month" class="form-control" value="{{ old('modal_exp_month') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Exp (YYYY)</label>
                        <input type="text" id="modal_exp_year" name="modal_exp_year" class="form-control" value="{{ old('modal_exp_year') }}">
                    </div>

                    <div class="col-md-2">
                        <label>CVC</label>
                        <input type="text" id="modal_cvc" name="modal_cvc" class="form-control" value="{{ old('modal_cvc') }}">
                    </div>

                    <input type="hidden" id="modal_payment_amount" value="35.00">
                    <!--<input type="hidden" id="modal_payment_amount" value="1.00">-->
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

@endsection



@section('script')
<script>
    $(document).ready(function() {

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


        function anyYesSelected() {
            return $('.q-radio:checked[value="1"]').length > 0;
        }

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

        // Show follow-up warning live
        $('.q-radio').on('change', function() {
            if (anyYesSelected()) {
                $('#followUpHint').slideDown();
            } else {
                $('#followUpHint').slideUp();
            }
        });

        // Intercept form submit → validate then open payment modal
        $('#cqiForm').on('submit', function(e) {
            e.preventDefault();

            let message = '';

            if (anyYesSelected())
                message += 'Patient answered "Yes" — follow up.\n';

            const dob = $('#date_of_birth').val();
            const age = calculateAge(dob);

            if (age !== null && age < 18)
                message += 'Patient appears under 18.\n';

            if (message !== '') {
                alert(message);
            } else {
                $('#paymentModal').modal('show');
            }
        });


        // Confirm payment button — generate fresh reCAPTCHA token just before submitting
        $('#confirmPaymentBtn').on('click', function() {

            // Disable button + show spinner
            $('#confirmPaymentBtn').prop('disabled', true).text("Processing...");
            $('#paymentProcessing').show();

            // Copy payment info into form
            $('<input type="hidden" name="card_number">').val($('#modal_card_number').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_exp_month">').val($('#modal_exp_month').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_exp_year">').val($('#modal_exp_year').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="card_cvc">').val($('#modal_cvc').val()).appendTo('#cqiForm');
            $('<input type="hidden" name="payment_amount">').val($('#modal_payment_amount').val()).appendTo('#cqiForm');

            // Get a fresh token immediately before submission so it doesn't expire
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config("services.recaptcha.site_key") }}', { action: 'submit_patient' })
                    .then(function(token) {
                        if ($('#recaptcha_token').length === 0) {
                            $('<input>').attr({
                                type: 'hidden',
                                id: 'recaptcha_token',
                                name: 'recaptcha_token',
                                value: token
                            }).appendTo('#cqiForm');
                        } else {
                            $('#recaptcha_token').val(token);
                        }
                        $('#cqiForm')[0].submit();
                    });
            });
        });
    });
</script>
@endsection