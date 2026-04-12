@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Analytics</h2>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">
                        Clinical Dashboard
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- ── Top Stats (always visible) ── --}}
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

                <hr>

                {{-- ── Sidebar + Content ── --}}
                <div class="row">

                    {{-- Sidebar --}}
                    <div class="col-md-2">
                        <div class="nav flex-column nav-pills" id="analyticsSidebar" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active text-start mb-1" id="tab-revenue" data-bs-toggle="pill"
                                    data-bs-target="#pane-revenue" type="button" role="tab"
                                    aria-controls="pane-revenue" aria-selected="true">
                                &#x1F4B5; Revenue Trend
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-tod" data-bs-toggle="pill"
                                    data-bs-target="#pane-tod" type="button" role="tab"
                                    aria-controls="pane-tod" aria-selected="false">
                                &#x1F552; Time of Day
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-geo" data-bs-toggle="pill"
                                    data-bs-target="#pane-geo" type="button" role="tab"
                                    aria-controls="pane-geo" aria-selected="false">
                                &#x1F5FA; Geography
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-return" data-bs-toggle="pill"
                                    data-bs-target="#pane-return" type="button" role="tab"
                                    aria-controls="pane-return" aria-selected="false">
                                &#x1F504; Return Rate
                            </button>
                            <a href="https://analytics.google.com" target="_blank" class="nav-link text-start mb-1">
                                &#x1F4CA; Google Analytics
                            </a>
                        </div>
                    </div>

                    {{-- Content panes --}}
                    <div class="col-md-10">
                        <div class="tab-content" id="analyticsContent">

                            {{-- Revenue Trend --}}
                            <div class="tab-pane fade show active" id="pane-revenue" role="tabpanel" aria-labelledby="tab-revenue">
                                <h5 class="mb-3">Revenue Trend — Last 12 Months</h5>
                                <canvas id="revenueTrendChart" height="80"></canvas>
                            </div>

                            {{-- Time of Day --}}
                            <div class="tab-pane fade" id="pane-tod" role="tabpanel" aria-labelledby="tab-tod">
                                <h5 class="mb-3">Submission Time of Day (All Time)</h5>
                                <canvas id="timeOfDayChart" height="80"></canvas>
                            </div>

                            {{-- Geography --}}
                            <div class="tab-pane fade" id="pane-geo" role="tabpanel" aria-labelledby="tab-geo">
                                <h5 class="mb-3">Patient Geography — All Time</h5>
                                @if($cityBreakdown->isEmpty())
                                    <div class="text-muted">No data available.</div>
                                @else
                                    <div style="position: relative; height: {{ max(300, $cityBreakdown->count() * 28) }}px;">
                                        <canvas id="geographyChart"></canvas>
                                    </div>
                                @endif
                            </div>

                            {{-- Return Rate --}}
                            <div class="tab-pane fade" id="pane-return" role="tabpanel" aria-labelledby="tab-return">
                                <h5 class="mb-3">Patient Return Rate — All Time</h5>
                                <div class="row g-3 mb-4">
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
                </div>{{-- end sidebar/content row --}}

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── Revenue Trend — init immediately (default visible pane) ────
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

    // ── Time of Day — lazy init on first show ──────────────────────
    let todInitialized = false;
    document.getElementById('tab-tod').addEventListener('shown.bs.tab', function () {
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

    // ── Geography — lazy init on first show ────────────────────────
    @if(!$cityBreakdown->isEmpty())
    let geoInitialized = false;
    document.getElementById('tab-geo').addEventListener('shown.bs.tab', function () {
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
