@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Thank you</h3>
    </div>

     <div class="card-body text-center">
         <h2>Thank you. Once evaluated for accuracy and safety you will receive your prescription within the next 48 hours.</h2>
     </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Payment Successful</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">Thank you for using StudioMeds. Your prescription will be emailed to you within 48 hours from <strong>admin@studiomeds.com</strong>.</p>
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
