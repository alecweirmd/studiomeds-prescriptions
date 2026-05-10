@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Thank you</h3>
    </div>

     <div class="card-body text-center">
         <p class="mb-0">Thank you for choosing StudioMeds. Check your email for your prescriptions.</p>
     </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Evaluation Submitted Successfully</h5>
            </div>
            <div class="modal-body py-4">
                <div class="text-center">
                    <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                </div>
                <p class="mt-3">Your prescriptions will be emailed to you from <strong>admin@studiomeds.com</strong> within a few hours. Here is what to expect:</p>
                @php
                    $isFacial = in_array($procedureType ?? null, ['lip_blush', 'eyeliner']);
                @endphp
                @if ($isFacial)
                <ol class="mb-0">
                    <li>Check your email including spam/junk folder</li>
                    <li>Your prescription will be for Zensa 5% cream</li>
                    <li>We recommend purchase from Amazon as this may not be available in every local drug store</li>
                    <li>Bring a printed or digital copy of your prescription and the medication to your appointment</li>
                </ol>
                @else
                <ol class="mb-0">
                    <li>Check your email including spam/junk folder</li>
                    <li>Your prescriptions will be for Aspercreme 4% Lidocaine Cream and Bactine Max Spray</li>
                    <li>Purchase both medications over the counter at Amazon, Walmart, or most pharmacies — no pharmacy visit required</li>
                    <li>Bring a printed or digital copy of your prescriptions and the medications to your appointment</li>
                </ol>
                @endif
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
@endsection
