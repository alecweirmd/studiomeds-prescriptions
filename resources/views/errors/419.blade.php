@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1 class="mb-4">Form timed out</h1>
    <p class="lead mb-4">We couldn't save what you started. Sorry about that. Start over below. If you keep getting timed out, email <a href="mailto:admin@studiomeds.com">admin@studiomeds.com</a> and we'll get you sorted.</p>
    <a href="/users/client_form" class="btn btn-primary btn-lg mt-3">Start over</a>
</div>
@endsection
