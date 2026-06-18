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
                                                @php $who = $name !== '' ? $name : 'this patient'; @endphp
                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm js-alert-action"
                                                    data-action-url="{{ url('/dashboard/alerts/' . $alert->id . '/recover') }}"
                                                    data-title="Confirm Recovery"
                                                    data-body="You are about to recover {{ $who }}{{ $sub->email ? ' (' . $sub->email . ')' : '' }}. This will generate the prescription PDF, send the approval email to the patient, and move them to the Approved tab. This action cannot be undone."
                                                    data-submit-label="Generate and Send Prescription"
                                                    data-submit-class="btn-success">
                                                    Generate and Send Prescription
                                                </button>
                                            </td>
                                        @elseif($alert->type === 'email_delivery_failed' && $sub)
                                            @php
                                                $name = trim(($sub->first_name ?? '') . ' ' . ($sub->last_name ?? ''));
                                                $who = $name !== '' ? $name : 'this patient';
                                                $meta = $alert->metadata ?? [];
                                                $failureReason = $meta['failure_reason'] ?? '—';
                                                $attemptCount = $meta['attempt_count'] ?? null;
                                                $failedAt = !empty($meta['failed_at'])
                                                    ? \Illuminate\Support\Carbon::parse($meta['failed_at'])->format('M j, Y g:i A')
                                                    : ($alert->created_at ? $alert->created_at->format('M j, Y g:i A') : '—');
                                            @endphp
                                            <td><span class="badge bg-warning text-dark">Email Delivery Failed</span></td>
                                            <td>{{ $name !== '' ? $name : 'Unknown' }}</td>
                                            <td>{{ $sub->email ?: '—' }}</td>
                                            <td colspan="2">
                                                <span class="text-danger">{{ $failureReason }}</span>
                                                @if($attemptCount !== null)
                                                    <span class="text-muted">(after {{ $attemptCount }} {{ \Illuminate\Support\Str::plural('attempt', $attemptCount) }})</span>
                                                @endif
                                            </td>
                                            <td>{{ $failedAt }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-primary btn-sm js-alert-action"
                                                        data-action-url="{{ url('/dashboard/alerts/' . $alert->id . '/resend-email') }}"
                                                        data-title="Resend Approval Email"
                                                        data-body="Resend the approval email to {{ $who }}{{ $sub->email ? ' (' . $sub->email . ')' : '' }}? This re-dispatches the prescription email and marks this alert resolved."
                                                        data-submit-label="Resend approval email"
                                                        data-submit-class="btn-primary">
                                                        Resend approval email
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary btn-sm js-alert-action"
                                                        data-action-url="{{ url('/dashboard/alerts/' . $alert->id . '/mark-resolved') }}"
                                                        data-title="Mark Alert Resolved"
                                                        data-body="Mark this email-delivery alert for {{ $who }}{{ $sub->email ? ' (' . $sub->email . ')' : '' }} as resolved? This does not resend any email — use it when the patient has been handled out of band. This action cannot be undone."
                                                        data-submit-label="Mark resolved"
                                                        data-submit-class="btn-secondary">
                                                        Mark resolved
                                                    </button>
                                                </div>
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

{{-- Generalized confirmation modal — data-driven, reused across every alert
     action (charge_no_record recovery, email-delivery resend, mark resolved).
     The trigger button carries the title/body/submit-label/submit-class and the
     POST action URL; the JS below wires them in before showing the modal. --}}
<div class="modal fade" id="alertActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertActionTitle">Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="alertActionBody" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="alertActionForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn" id="alertActionSubmit">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    (function () {
        var modalEl = document.getElementById('alertActionModal');
        var modal = new bootstrap.Modal(modalEl);
        var form = document.getElementById('alertActionForm');
        var titleEl = document.getElementById('alertActionTitle');
        var bodyEl = document.getElementById('alertActionBody');
        var submitBtn = document.getElementById('alertActionSubmit');
        var defaultSubmitLabel = 'Confirm';

        document.querySelectorAll('.js-alert-action').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.setAttribute('action', btn.getAttribute('data-action-url'));
                titleEl.textContent = btn.getAttribute('data-title') || 'Confirm';
                bodyEl.textContent = btn.getAttribute('data-body') || '';

                defaultSubmitLabel = btn.getAttribute('data-submit-label') || 'Confirm';
                submitBtn.textContent = defaultSubmitLabel;

                // Reset to a clean enabled button each open (a prior submit may
                // have left it disabled if the modal was reused without reload).
                submitBtn.disabled = false;
                submitBtn.className = 'btn ' + (btn.getAttribute('data-submit-class') || 'btn-primary');

                modal.show();
            });
        });

        // Guard against double-submit on the (often irreversible) action.
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Working…';
        });
    })();
</script>
@endsection
