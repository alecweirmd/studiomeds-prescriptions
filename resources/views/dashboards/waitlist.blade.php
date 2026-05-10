@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Lip/Eyeliner Waitlist</h2>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">Clinical Dashboard</a>
                    <a href="{{ url('/dashboard/analytics') }}" class="btn btn-secondary btn-sm">
                        &#x1F4CA; Analytics
                    </a>
                    <a href="{{ url('/dashboard/marketing') }}" class="btn btn-secondary btn-sm">
                        &#x1F4E2; Marketing
                    </a>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted mb-3">
                    Interim view for the lip/eyeliner launch announcement. Will be replaced by Phase 2 admin dashboard.
                </p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fs-4 fw-bold">
                        {{ $entries->count() }} signup{{ $entries->count() === 1 ? '' : 's' }}
                    </div>
                    <a href="{{ url('/dashboard/waitlist/export') }}" class="btn btn-success btn-sm">
                        Export CSV
                    </a>
                </div>

                @if($entries->isEmpty())
                    <div class="text-muted">No waitlist signups yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Email</th>
                                    <th>Date Captured</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $i => $entry)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $entry->email }}</td>
                                        <td>{{ $entry->created_at ? $entry->created_at->format('M j, Y g:i A') : '—' }}</td>
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

@endsection
