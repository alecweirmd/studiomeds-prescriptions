@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="mb-0">Sponsored Artist Interest</h2>
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
                    Interim view for the Sponsored Artist program interest capture. Will be replaced by Phase 2 partner dashboard.
                </p>

                @php
                    $tattooCount = $entries->where('source_page', 'tattoo')->count();
                    $pmuCount = $entries->where('source_page', 'pmu')->count();
                    $totalCount = $entries->count();
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="btn-group" role="group" aria-label="Filter by source page">
                        <button type="button" class="btn btn-outline-secondary btn-sm active" data-sponsorship-filter="all">
                            All ({{ $totalCount }})
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-sponsorship-filter="tattoo">
                            Tattoo ({{ $tattooCount }})
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-sponsorship-filter="pmu">
                            PMU ({{ $pmuCount }})
                        </button>
                    </div>
                    <a href="{{ url('/dashboard/sponsorship/export') }}" class="btn btn-success btn-sm" data-sponsorship-export>
                        Export CSV
                    </a>
                </div>

                @if($entries->isEmpty())
                    <div class="text-muted">No entries yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" data-sponsorship-table>
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Shop Name</th>
                                    <th>Location</th>
                                    <th>Focus</th>
                                    <th>Source</th>
                                    <th>Social</th>
                                    <th>How Heard</th>
                                    <th>Date Captured</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $i => $entry)
                                    <tr data-source-page="{{ $entry->source_page }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $entry->name }}</td>
                                        <td>{{ $entry->email }}</td>
                                        <td>{{ $entry->shop_name }}</td>
                                        <td>{{ $entry->shop_location }}</td>
                                        <td>{{ ucfirst($entry->procedure_focus) }}</td>
                                        <td>{{ ucfirst($entry->source_page) }}</td>
                                        <td>{{ $entry->social_handle ?: '—' }}</td>
                                        <td>{{ $entry->how_did_you_hear ?: '—' }}</td>
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

<script>
    (function () {
        var filterButtons = document.querySelectorAll('[data-sponsorship-filter]');
        var rows = document.querySelectorAll('[data-sponsorship-table] tbody tr');
        var exportLink = document.querySelector('[data-sponsorship-export]');
        var baseExportUrl = exportLink ? exportLink.getAttribute('href') : null;

        filterButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var filter = btn.getAttribute('data-sponsorship-filter');

                filterButtons.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                rows.forEach(function (row) {
                    var source = row.getAttribute('data-source-page');
                    var show = filter === 'all' || source === filter;
                    row.style.display = show ? '' : 'none';
                });

                if (exportLink && baseExportUrl) {
                    exportLink.setAttribute(
                        'href',
                        filter === 'all' ? baseExportUrl : baseExportUrl + '?source=' + encodeURIComponent(filter)
                    );
                }
            });
        });
    })();
</script>

@endsection
