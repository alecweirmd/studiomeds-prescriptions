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

                {{-- ── This Month Stats ── --}}
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

                {{-- ── Revenue Trend (always visible) ── --}}
                <h5 class="mb-3">Revenue Trend — Last 12 Months</h5>
                <div class="mb-4">
                    <canvas id="revenueTrendChart" height="80"></canvas>
                </div>

                {{-- ── Time of Day (collapsible) ── --}}
                <div class="mb-2">
                    <button class="btn btn-light w-100 text-start border d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse"
                            data-bs-target="#sectionTimeOfDay"
                            aria-expanded="false">
                        <span>&#x1F552; Submission Time of Day</span>
                        <span class="text-muted small">click to expand</span>
                    </button>
                    <div class="collapse" id="sectionTimeOfDay">
                        <div class="mt-3 mb-2">
                            <canvas id="timeOfDayChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                {{-- ── Patient Geography (collapsible) ── --}}
                <div class="mb-2 mt-2">
                    <button class="btn btn-light w-100 text-start border d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse"
                            data-bs-target="#sectionGeography"
                            aria-expanded="false">
                        <span>&#x1F5FA; Patient Geography</span>
                        <span class="text-muted small">click to expand</span>
                    </button>
                    <div class="collapse" id="sectionGeography">
                        @if($cityBreakdown->isEmpty())
                            <div class="text-muted mt-3">No data available.</div>
                        @else
                            <div class="mt-3" style="position: relative; height: {{ max(300, $cityBreakdown->count() * 28) }}px;">
                                <canvas id="geographyChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Patient Return Rate (collapsible) ── --}}
                <div class="mb-2 mt-2">
                    <button class="btn btn-light w-100 text-start border d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse"
                            data-bs-target="#sectionReturnRate"
                            aria-expanded="false">
                        <span>&#x1F504; Patient Return Rate</span>
                        <span class="text-muted small">click to expand</span>
                    </button>
                    <div class="collapse" id="sectionReturnRate">
                        <div class="row g-3 mt-2 mb-3">
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <div class="fs-2 fw-bold">{{ $returningCount }}</div>
                                        <div class="text-muted">Unique Returning Patients</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <div class="fs-2 fw-bold">{{ $avgDaysBetween }}</div>
                                        <div class="text-muted">Avg Days Between 1st &amp; 2nd Submission</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($returningList))
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Submission Dates</th>
                                            <th>Days Between 1st &amp; 2nd</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($returningList as $r)
                                        <tr>
                                            <td>{{ $r['name'] }}</td>
                                            <td>{{ $r['email'] }}</td>
                                            <td>{{ implode(', ', $r['submissions']) }}</td>
                                            <td>{{ $r['days'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted">No returning patients found.</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── Revenue Trend ──────────────────────────────────────────────
    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($revenueTrend, 'label')) !!},
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode(array_column($revenueTrend, 'revenue')) !!},
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '$' + v }
                }
            }
        }
    });

    // ── Time of Day (lazy — init on first expand) ──────────────────
    let todInitialized = false;
    document.getElementById('sectionTimeOfDay').addEventListener('show.bs.collapse', function () {
        if (todInitialized) return;
        todInitialized = true;
        const hourLabels = Array.from({length: 24}, (_, i) => {
            if (i === 0)  return '12am';
            if (i < 12)  return i + 'am';
            if (i === 12) return '12pm';
            return (i - 12) + 'pm';
        });
        new Chart(document.getElementById('timeOfDayChart'), {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'Submissions',
                    data: {!! json_encode(array_values($hourCounts)) !!},
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });

    // ── Geography (lazy — init on first expand) ────────────────────
    @if(!$cityBreakdown->isEmpty())
    let geoInitialized = false;
    document.getElementById('sectionGeography').addEventListener('show.bs.collapse', function () {
        if (geoInitialized) return;
        geoInitialized = true;
        new Chart(document.getElementById('geographyChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($cityBreakdown->keys()->all()) !!},
                datasets: [{
                    label: 'Patients',
                    data: {!! json_encode($cityBreakdown->values()->all()) !!},
                    backgroundColor: 'rgba(255, 153, 0, 0.7)',
                    borderColor: 'rgba(255, 153, 0, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
    @endif
</script>
@endsection
