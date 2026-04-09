@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h2 class="mb-0" style="font-size:1.4rem;">Flagged Submissions</h2>
                <span class="badge bg-light text-danger">{{ $flagged->count() }} record(s)</span>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    These patients answered <strong>Yes</strong> to one or more medical screening questions,
                    acknowledged the warning, then completed payment. A PDF audit record was saved for each.
                </p>

                @if($flagged->isEmpty())
                    <div class="text-muted">No flagged submissions on record.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-danger">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Acknowledged At</th>
                                <th>Triggered Questions</th>
                                <th>Submitted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flagged as $ack)
                            @php $patient = $ack->patient; @endphp
                            <tr>
                                <td>{{ $patient->first_name ?? '—' }} {{ $patient->last_name ?? '' }}</td>
                                <td>{{ $patient->email ?? '—' }}</td>
                                <td><code>{{ $ack->ip_address }}</code></td>
                                <td>{{ $ack->acknowledged_at->format('m/d/Y g:i A') }}</td>
                                <td>
                                    @foreach($ack->triggered_questions as $q)
                                        <span class="badge bg-danger me-1">{{ $questionLabels[$q] ?? $q }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $patient ? $patient->created_at->format('m/d/Y') : '—' }}</td>
                                <td>
                                    @if($patient)
                                    <a href="{{ url('/users/submitted_cqi/' . $patient->id) }}" class="btn btn-sm btn-primary">View CQI</a>
                                    @endif
                                    @if($ack->pdf_path)
                                    <a href="{{ url('/dashboard/flagged_pdf/' . $ack->id) }}" class="btn btn-sm btn-outline-danger">Download PDF</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <div class="card-footer">
                <a href="{{ url('/dashboard') }}" class="btn btn-secondary btn-sm">&larr; Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

@endsection
