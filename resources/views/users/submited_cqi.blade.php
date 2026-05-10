@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Patient Details / CQI Screening</h3>
    </div>
    <div class="container py-4">
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
                <strong>Important:</strong> Patient answered "Yes" to one or more questions — please follow up with an in-person medical provider for further evaluation.
            </div>

            @php
                $procedureLabels = [
                    'tattoo'    => 'Tattoo',
                    'brow_pmu'  => 'Brow PMU',
                    'eyeliner'  => 'Eyeliner',
                    'lip_blush' => 'Lip Blush',
                ];
                $procedureLabel = $procedureLabels[$patient->procedure_type] ?? ($patient->procedure_type ?: 'Not specified');
            @endphp

            <!-- Procedure Type -->
            <div class="alert alert-info p-3 mb-3">
                <h4 class="mb-0"><strong>Procedure:</strong> {{ $procedureLabel }}</h4>
            </div>

            <!-- Artist Information Section -->
            <div class="row g-3 p-2">
                <h3>Artist Information</h3>
                <div class="col-md-6">
                    <h4><strong>Artist Name:</strong> @if($patient->artist->artist_name != NULL)
                        <h4>{{ $patient->artist->artist_name }}</h4>
                        @else
                        <h4>{{ $patient->artist->first_name }} {{ $patient->artist->last_name }}</h4>
                        @endif</h4>
                </div>
                <div class="col-md-6">
                    <h4><strong>Artist Shop:</strong> {{ $patient->artist->name_of_shop }}</h4>
                </div>
            </div>

            <!-- User Information Section -->
            <div class="row g-3 p-2">
                <h3>User Information</h3>
                <div class="col-md-4">
                    <p><strong>First Name:</strong> {{ $patient->first_name }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Last Name:</strong> {{ $patient->last_name }}</p>
                </div>     
                <div class="col-md-4">
                    <p><strong>Date of Birth:</strong> {{ \Carbon\Carbon::parse($patient->date_of_birth)->format('F j, Y') }}</p>
                </div> 
            </div>
            <div class="row g-3 p-2">
                <div class="col-md-4">
                    <p><strong>Email Address:</strong> {{ $patient->email }}</p>
                </div>
                <div class="col-md-4">
                   
                </div>     
                <div class="col-md-4">
                   
                </div> 
            </div>

            @php
                $resolveImageUrl = function ($path) {
                    if (empty($path)) {
                        return null;
                    }
                    if (preg_match('#^https?://#i', $path)) {
                        return $path;
                    }
                    $clean = ltrim($path, '/');
                    return str_starts_with($clean, 'storage/')
                        ? asset($clean)
                        : asset('storage/' . $clean);
                };
                $licenseUrl = $resolveImageUrl($patient->drivers_license);
                $selfieUrl  = $resolveImageUrl($patient->patient_photo);
            @endphp

            <!-- Artist Information Section -->
            <div class="row g-3 p-2">
                <h3>Artist Information</h3>
                <div class="col-md-6">
                    <h4><strong>Drivers License:</strong></h4>
                    @if($licenseUrl)
                        <img class="img-fluid" src="{{ $licenseUrl }}" alt="Driver's license" />
                    @else
                        <p class="text-muted">No image uploaded.</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h4><strong>Patient Selfie:</strong></h4>
                    @if($selfieUrl)
                        <img class="img-fluid" src="{{ $selfieUrl }}" alt="Patient selfie" />
                    @else
                        <p class="text-muted">No image uploaded.</p>
                    @endif
                </div>
            </div>

            <!-- Mailing Address Section -->
            <div class="row g-3 p-2">
                <h3>Mailing Address</h3>
                <div class="col-md-4">
                    <p><strong>Street Address:</strong> {{ $patient->street_address }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>City:</strong> {{ $patient->city }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>State:</strong> {{ $patient->state }}</p>
                </div>
            </div>

            <div class="row g-3 p-2">
                <div class="col-md-6">
                    <p><strong>ZIP or Postal Code:</strong> {{ $patient->zip }}</p>
                </div>
            </div>

            <!-- Medical History Section -->
            <h5>Section 1: Allergies / Sensitivities</h5>
            <div class="mb-3">
                <p><strong>1. Have you ever had an allergic reaction to lidocaine or other “-caine” anesthetics (such as benzocaine, prilocaine, or tetracaine)?</strong> 
                {{ $patient->lidocaine == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <!-- Additional Sections Here: Skin Conditions, Medical History, etc. -->
            <h5>Section 2: Skin Conditions / Open Wounds</h5>
            <div class="mb-3">
                <p><strong>3. Do you currently have broken skin, open wounds, or skin infections at the area where the topical will be applied?</strong>
                {{ $patient->broken_skin == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>4. Do you have a history of severe eczema, psoriasis, or other skin conditions at the area to be treated?</strong>
                {{ $patient->eczema == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <h5>Section 3: Medical History</h5>
            <div class="mb-3">
                <p><strong>5. Do you have any heart rhythm problems (arrhythmias) or heart block?</strong>
                {{ $patient->heart_rhythm == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>6. Do you have severe liver disease?</strong>
                {{ $patient->liver_disease == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>7. Have you ever experienced seizures related to medications or anesthetics?</strong>
                {{ $patient->seizures == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>8. Are you currently pregnant or breastfeeding?</strong>
                {{ $patient->pregnant == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <h5>Section 4: Medications / Substances</h5>
            <div class="mb-3">
                <p><strong>9. Are you currently taking any antiarrhythmic medications (such as amiodarone, mexiletine, quinidine)?</strong>
                {{ $patient->antiarrhythmic == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>10. Are you currently taking any medications for seizures or nerve pain that your provider has warned may interact with anesthetics?</strong>
                {{ $patient->seizure_meds == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <h5>Section 5: Past Reactions / Complications</h5>
            <div class="mb-3">
                <p><strong>11. Have you ever fainted, felt dizzy, or had a severe reaction when using local anesthetics before?</strong>
                {{ $patient->fainted == 1 ? 'Yes' : 'No' }}</p>
            </div>

            <div class="mb-3">
                <p><strong>12. Have you ever been told you have a condition called methemoglobinemia or a blood disorder affecting oxygen carrying capacity?</strong>
                {{ $patient->methemoglobinemia == 1 ? 'Yes' : 'No' }}</p>
            </div>

            @php
                $cqi = $patient->patientsCQI;
                $hasLipAddOn = $cqi && $cqi->lip_cold_sore_active !== null;
                $hasEyelinerAddOn = $cqi && (
                    $cqi->eye_infection_active   !== null ||
                    $cqi->recent_eye_surgery     !== null ||
                    $cqi->contacts_cannot_remove !== null ||
                    $cqi->severe_dry_eye         !== null
                );
            @endphp

            @if($hasLipAddOn || $hasEyelinerAddOn)
                <h5>Section 6: Procedure-Specific Questions</h5>

                @if($hasLipAddOn)
                <div class="mb-3">
                    <p><strong>13. Do you currently have an active cold sore, fever blister, or herpes simplex outbreak on or near your lips?</strong>
                    {{ $cqi->lip_cold_sore_active == 1 ? 'Yes' : 'No' }}</p>
                </div>
                @endif

                @if($hasEyelinerAddOn)
                <div class="mb-3">
                    <p><strong>13. Do you currently have an active eye infection, blepharitis, conjunctivitis (pink eye), or stye?</strong>
                    {{ $cqi->eye_infection_active == 1 ? 'Yes' : 'No' }}</p>
                </div>
                <div class="mb-3">
                    <p><strong>14. Have you had eye surgery (LASIK, PRK, cataract, or other) within the past 6 months?</strong>
                    {{ $cqi->recent_eye_surgery == 1 ? 'Yes' : 'No' }}</p>
                </div>
                <div class="mb-3">
                    <p><strong>15. Do you currently wear contact lenses and are unable to remove them and switch to glasses for the day of your procedure and 24 hours afterward?</strong>
                    {{ $cqi->contacts_cannot_remove == 1 ? 'Yes' : 'No' }}</p>
                </div>
                <div class="mb-3">
                    <p><strong>16. Do you have severe dry eye syndrome that requires daily prescription eye drops or punctal plugs?</strong>
                    {{ $cqi->severe_dry_eye == 1 ? 'Yes' : 'No' }}</p>
                </div>
                @endif
            @endif

            @if($patient->patientsCQI && $patient->patientsCQI->status == 0)
            <div class="mt-4">
                <a class="btn btn-success approve" data-id="{{ $patient->id }}">Approve</a>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to approve this patient?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmApprove" class="btn btn-success">Approve</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).on('click', '.approve', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $('#confirmApprove').attr('href', '/dashboard/approve_patient/' + id);
        $('#approveModal').modal('show');
    });
</script>
@endsection
