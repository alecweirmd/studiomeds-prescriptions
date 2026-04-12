@extends('layouts.app')

@section('content')

<div class="row gy-4">

    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Analytics</h2>
                <div class="d-flex gap-2">
                    <a href="https://analytics.google.com" target="_blank" class="btn btn-secondary btn-sm">
                        &#x1F4CA; Google Analytics
                    </a>
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">
                        &larr; Back to Dashboard
                    </a>
                    <a href="{{ url('/dashboard/flagged_submissions') }}" class="btn btn-danger btn-sm">
                        &#9888; Flagged Submissions
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- This Month Stats --}}
                <h5 class="mb-3">This Month</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="fs-2 fw-bold">{{ $total }}</div>
                                <div class="text-muted">Total Submissions</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="fs-2 fw-bold text-success">${{ number_format($revenue, 2) }}</div>
                                <div class="text-muted">Revenue ({{ $approved }} approved &times; $35)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="fs-2 fw-bold text-success">{{ $approvalRate }}%</div>
                                <div class="text-muted">Approval Rate</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="fs-2 fw-bold text-danger">{{ $rejectionRate }}%</div>
                                <div class="text-muted">Rejection Rate</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- City Breakdown --}}
                <h5 class="mb-3">Patient City Breakdown (All Time)</h5>
                @if($cityBreakdown->isEmpty())
                    <div class="text-muted">No data available.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>City</th>
                                    <th>Patients</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cityBreakdown as $city => $count)
                                <tr>
                                    <td>{{ $city }}</td>
                                    <td>{{ $count }}</td>
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
