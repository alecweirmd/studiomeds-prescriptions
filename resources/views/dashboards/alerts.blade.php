@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0">&#9888; Alerts</h2>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">Clinical Dashboard</a>
                    <a href="{{ url('/dashboard/analytics') }}" class="btn btn-secondary btn-sm">
                        &#x1F4CA; Analytics
                    </a>
                    <a href="{{ url('/dashboard/marketing') }}" class="btn btn-secondary btn-sm">
                        &#x1F4E2; Marketing
                    </a>
                    <a href="{{ url('/dashboard/sponsorship') }}" class="btn btn-secondary btn-sm">
                        &#x1F4DD; Sponsorship
                    </a>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    Items requiring admin intervention. Charge-but-no-record alerts are patients
                    whose payment succeeded but whose record failed to save — recover them below to
                    generate the prescription, send the approval email, and move them to Approved.
                </p>

                @if($alerts->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div style="font-size: 2rem;">&#10003;</div>
                        No alerts requiring action.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Procedure</th>
                                    <th>Transaction ID</th>
                                    <th>Date Captured</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $i => $alert)
                                    @php $sub = $alert->alertable; @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        @if($alert->type === 'charge_no_record' && $sub)
                                            @php
                                                $name = trim(($sub->first_name ?? '') . ' ' . ($sub->last_name ?? ''));
                                                $procedure = $sub->procedure_type
                                                    ? ucwords(str_replace('_', ' ', $sub->procedure_type))
                                                    : '—';
                                            @endphp
                                            <td><span class="badge bg-danger">Charge — No Record</span></td>
                                            <td>{{ $name !== '' ? $name : 'Unknown' }}</td>
                                            <td>{{ $sub->email ?: '—' }}</td>
                                            <td>{{ $procedure }}</td>
                                            <td><code>{{ $sub->transaction_id }}</code></td>
                                            <td>{{ $sub->charged_at ? $sub->charged_at->format('M j, Y g:i A') : ($alert->created_at ? $alert->created_at->format('M j, Y g:i A') : '—') }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm js-recover"
                                                    data-recover-url="{{ url('/dashboard/alerts/' . $alert->id . '/recover') }}"
                                                    data-name="{{ $name !== '' ? $name : 'this patient' }}"
                                                    data-email="{{ $sub->email }}">
                                                    Generate and Send Prescription
                                                </button>
                                            </td>
                                        @else
                                            {{-- Forward-compatible fallback for future alert types --}}
                                            <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $alert->type)) }}</span></td>
                                            <td colspan="4" class="text-muted">No handler for this alert type in this view.</td>
                                            <td>{{ $alert->created_at ? $alert->created_at->format('M j, Y g:i A') : '—' }}</td>
                                            <td>—</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Confirmation modal — mirrors the approve/resend confirmation pattern.
     This is an irreversible patient-facing action (sends the approval email). --}}
<div class="modal fade" id="recoverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Recovery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to recover <strong id="recoverName">this patient</strong> (<span id="recoverEmail"></span>).</p>
                <p>This will generate the prescription PDF, send the approval email to the patient,
                   and move them to the Approved tab. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="recoverForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-success">Generate and Send Prescription</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    (function () {
        var modalEl = document.getElementById('recoverModal');
        var modal = new bootstrap.Modal(modalEl);
        var form = document.getElementById('recoverForm');
        var nameEl = document.getElementById('recoverName');
        var emailEl = document.getElementById('recoverEmail');
        var submitBtn = form.querySelector('button[type="submit"]');

        document.querySelectorAll('.js-recover').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.setAttribute('action', btn.getAttribute('data-recover-url'));
                nameEl.textContent = btn.getAttribute('data-name');
                emailEl.textContent = btn.getAttribute('data-email') || '';
                modal.show();
            });
        });

        // Guard against double-submit on the irreversible action.
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending…';
        });
    })();
</script>
@endsection
