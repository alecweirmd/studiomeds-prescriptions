@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <nav aria-label="breadcrumb" class="float-start">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Register Artist</li>
            </ol>
        </nav>        
    </div>

    <form method="post" enctype="multipart/form-data" action="{{ url('users/store_artist') }}" id="artistForm">
        @csrf

        <div class="card-body">
            <h3>User Information</h3>
            <div class="row g-3 p-2">
                <div class="col">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div class="col">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
                <div class="col">
                    <label for="artist_name" class="form-label">Artist Name</label>
                    <input type="text" class="form-control" id="artist_name" name="artist_name" value="{{ old('artist_name') }}">
                </div>
                <div class="col">
                    <label for="name_of_shop" class="form-label">Name of Shop</label>
                    <input type="text" class="form-control" id="name_of_shop" name="name_of_shop" value="{{ old('name_of_shop') }}">
                </div>
            </div>

            <h3>Contact</h3>
            <div class="row g-3 p-2">
                <div class="col">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="col">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                </div>
            </div>

            <h3>Mailing Address</h3>
            <div class="row g-3 p-2">
                <div class="col">
                    <label for="street_address" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="street_address" name="street_address" value="{{ old('street_address') }}" required>
                </div>
                <div class="col">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" required>
                </div>
                <div class="col">
                    <label for="state" class="form-label">State</label>
                    <select class="form-select select2" id="state" name="state" required>
                        <option></option>
                        @foreach($states as $state)
                            <option value="{{$state->id}}" @if(old('state') == $state->id) selected @endif>{{$state->full}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="zip" class="form-label">ZIP / Postal Code</label>
                    <input type="text" class="form-control" id="zip" name="zip" value="{{ old('zip') }}" required>
                </div>
            </div>

            <h3>Documents</h3>
            <div class="row g-3 p-2">
                <div class="col">
                    <label for="drivers_license" class="form-label">Drivers License</label>
                    <input type="file" name="drivers_license" class="form-control" required>
                </div>
                <div class="col">
                    <label for="selfie_photo" class="form-label">Selfie</label>
                    <input type="file" name="selfie_photo" class="form-control" required>
                </div>
            </div>

            <h3>Payment</h3>
            <p class="text-muted">A $95 subscription will be charged immediately and then every month.</p>
            <div class="row g-3 p-2">
                <div class="col">
                    <label>Card Number</label>
                    <input type="text" name="card_number" class="form-control" required>
                </div>
                <div class="col">
                    <label>Exp Month (MM)</label>
                    <input type="text" name="exp_month" class="form-control" required>
                </div>
                <div class="col">
                    <label>Exp Year (YYYY)</label>
                    <input type="text" name="exp_year" class="form-control" required>
                </div>
                <div class="col">
                    <label>CVV</label>
                    <input type="text" name="cvv" class="form-control" required>
                </div>
            </div>

            <!-- Accept.js hidden token fields -->
            <input type="hidden" name="dataValue" id="dataValue">
            <input type="hidden" name="dataDescriptor" id="dataDescriptor">
        </div>

        <div class="card-footer text-end">
            <button type="button" id="payButton" class="btn btn-success">Submit & Subscribe $95/month</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script type="text/javascript" src="https://jstest.authorize.net/v1/Accept.js" charset="utf-8"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#payButton').click(function(e) {
        e.preventDefault();

        var authData = {
            clientKey: "{{ config('services.authorize.client_key') }}",
            apiLoginID: "{{ config('services.authorize.login_id') }}"
        };

        var cardData = {
            cardNumber: $('input[name="card_number"]').val(),
            month: $('input[name="exp_month"]').val(),
            year: $('input[name="exp_year"]').val(),
            cardCode: $('input[name="cvv"]').val()
        };
        
        var secureData = { authData: authData, cardData: cardData };

        Accept.dispatchData(secureData, function(response) {
            if (response.messages.resultCode === "Error") {
                var errors = response.messages.message.map(msg => msg.text).join("\n");
                alert("Payment Error:\n" + errors);
            } else {
                $('#dataValue').val(response.opaqueData.dataValue);
                $('#dataDescriptor').val(response.opaqueData.dataDescriptor);
                $('#artistForm').submit();
            }
        });
    });
});
</script>
@endsection
