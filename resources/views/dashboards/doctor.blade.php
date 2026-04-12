@extends('layouts.app')

@section('content')

<div class="row gy-4">

    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Submitted Client Forms</h2>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dashboard/analytics') }}" class="btn btn-secondary btn-sm">
                        &#x1F4CA; Analytics
                    </a>
                </div>
            </div>

            <div class="card-body">
                @php
                $statusLabels = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Rejected'
                ];
                @endphp

                <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                    @foreach($statusLabels as $status => $label)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif"
                            id="tab-{{ $status }}"
                            data-bs-toggle="tab"
                            data-bs-target="#content-{{ $status }}"
                            type="button"
                            role="tab"
                            aria-controls="content-{{ $status }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $label }}
                        </button>
                    </li>
                    @endforeach
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-flagged" data-bs-toggle="tab"
                                data-bs-target="#content-flagged" type="button" role="tab"
                                aria-controls="content-flagged" aria-selected="false">
                            &#9888; Flagged
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3 border border-top-0" id="patientTabsContent">
                    @foreach($statusLabels as $status => $label)
                    <div class="tab-pane fade @if($loop->first) show active @endif"
                        id="content-{{ $status }}"
                        role="tabpanel"
                        aria-labelledby="tab-{{ $status }}">

                        @if($status == 0)
                            {{-- ── Pending: unchanged ── --}}
                            @php $group = $patients[$status] ?? collect(); @endphp
                            @if($group->isEmpty())
                            <div class="text-muted">No patients in this status.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Artist Name</th>
                                            <th>CQI Status</th>
                                            <th>Submitted On</th>
                                            <th>Actions <span class="text-end"><a class="btn btn-sm btn-success approve_all">Approve All</a></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group as $p)
                                        <tr>
                                            <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                                            <td>
                                                @if($p->artist_id && $p->artist)
                                                @if($p->artist->artist_name != NULL)
                                                <p>{{ $p->artist->artist_name }}</p>
                                                @else
                                                <p>{{ $p->artist->first_name }} {{ $p->artist->last_name }}</p>
                                                @endif
                                                @else
                                                <p>{{ $p->artist_name }}</p>
                                                @endif
                                            </td>
                                            <td>{{ $p->patientsCQI ? $statusLabels[$p->patientsCQI->status] : 'Pending' }}</td>
                                            <td>{{ $p->created_at->format('m/d/Y') }}</td>
                                            <td>
                                                <a href="{{ url('/users/submitted_cqi/' . $p->id) }}" class="btn btn-sm btn-primary">View CQI</a>
                                                @if($p->patientsCQI && $p->patientsCQI->status == 0)
                                                <a class="btn btn-sm btn-success approve" data-id="{{ $p->id }}">Approve</a>
                                                <a class="btn btn-sm btn-danger reject" data-id="{{ $p->id }}">Reject</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        @else
                            {{-- ── Approved / Rejected: with monthly/yearly archiving ── --}}
                            @php
                                $statusArchive = $archiveData[$status] ?? ['current' => collect(), 'archive' => []];
                                $current = $statusArchive['current'];
                                $archive = $statusArchive['archive'];
                            @endphp

                            @if($current->isEmpty() && empty($archive))
                            <div class="text-muted">No patients in this status.</div>
                            @else

                                {{-- Current month rows --}}
                                @if($current->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Artist Name</th>
                                                <th>CQI Status</th>
                                                <th>Submitted On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($current as $p)
                                            <tr>
                                                <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                                                <td>
                                                    @if($p->artist_id && $p->artist)
                                                    @if($p->artist->artist_name != NULL)
                                                    <p>{{ $p->artist->artist_name }}</p>
                                                    @else
                                                    <p>{{ $p->artist->first_name }} {{ $p->artist->last_name }}</p>
                                                    @endif
                                                    @else
                                                    <p>{{ $p->artist_name }}</p>
                                                    @endif
                                                </td>
                                                <td>{{ $p->patientsCQI ? $statusLabels[$p->patientsCQI->status] : 'Pending' }}</td>
                                                <td>{{ $p->created_at->format('m/d/Y') }}</td>
                                                <td>
                                                    <a href="{{ url('/users/submitted_cqi/' . $p->id) }}" class="btn btn-sm btn-primary">View CQI</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif

                                {{-- Archive folders --}}
                                @foreach($archive as $folder)
                                    @if($folder['type'] === 'month')
                                        @php $folderId = 'arc_' . $status . '_' . str_replace('-', '_', $folder['key']); @endphp
                                        <div class="mt-1">
                                            <button class="btn btn-light btn-sm w-100 text-start border"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $folderId }}"
                                                    aria-expanded="false">
                                                &#x1F4C1; {{ $folder['label'] }} ({{ $folder['patients']->count() }} {{ $folder['patients']->count() === 1 ? 'patient' : 'patients' }})
                                            </button>
                                            <div class="collapse" id="{{ $folderId }}">
                                                <div class="table-responsive ms-3 mt-1">
                                                    <table class="table table-striped table-bordered table-sm align-middle">
                                                        <thead>
                                                            <tr><th>Name</th><th>Artist Name</th><th>CQI Status</th><th>Submitted On</th><th>Actions</th></tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($folder['patients'] as $p)
                                                            <tr>
                                                                <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                                                                <td>
                                                                    @if($p->artist_id && $p->artist)
                                                                    @if($p->artist->artist_name != NULL)
                                                                    <p>{{ $p->artist->artist_name }}</p>
                                                                    @else
                                                                    <p>{{ $p->artist->first_name }} {{ $p->artist->last_name }}</p>
                                                                    @endif
                                                                    @else
                                                                    <p>{{ $p->artist_name }}</p>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $p->patientsCQI ? $statusLabels[$p->patientsCQI->status] : 'Pending' }}</td>
                                                                <td>{{ $p->created_at->format('m/d/Y') }}</td>
                                                                <td><a href="{{ url('/users/submitted_cqi/' . $p->id) }}" class="btn btn-sm btn-primary">View CQI</a></td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Year folder --}}
                                        @php $yearFolderId = 'arc_' . $status . '_' . $folder['key']; @endphp
                                        <div class="mt-1">
                                            <button class="btn btn-secondary btn-sm w-100 text-start"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $yearFolderId }}"
                                                    aria-expanded="false">
                                                &#x1F4C1; {{ $folder['label'] }} ({{ $folder['count'] }} {{ $folder['count'] === 1 ? 'patient' : 'patients' }})
                                            </button>
                                            <div class="collapse" id="{{ $yearFolderId }}">
                                                <div class="ms-3 mt-1">
                                                    @foreach($folder['months'] as $monthFolder)
                                                        @php $mFolderId = 'arc_' . $status . '_' . str_replace('-', '_', $monthFolder['key']); @endphp
                                                        <div class="mt-1">
                                                            <button class="btn btn-light btn-sm w-100 text-start border"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#{{ $mFolderId }}"
                                                                    aria-expanded="false">
                                                                &#x1F4C1; {{ $monthFolder['label'] }} ({{ $monthFolder['patients']->count() }} {{ $monthFolder['patients']->count() === 1 ? 'patient' : 'patients' }})
                                                            </button>
                                                            <div class="collapse" id="{{ $mFolderId }}">
                                                                <div class="table-responsive ms-3 mt-1">
                                                                    <table class="table table-striped table-bordered table-sm align-middle">
                                                                        <thead>
                                                                            <tr><th>Name</th><th>Artist Name</th><th>CQI Status</th><th>Submitted On</th><th>Actions</th></tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($monthFolder['patients'] as $p)
                                                                            <tr>
                                                                                <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                                                                                <td>
                                                                                    @if($p->artist_id && $p->artist)
                                                                                    @if($p->artist->artist_name != NULL)
                                                                                    <p>{{ $p->artist->artist_name }}</p>
                                                                                    @else
                                                                                    <p>{{ $p->artist->first_name }} {{ $p->artist->last_name }}</p>
                                                                                    @endif
                                                                                    @else
                                                                                    <p>{{ $p->artist_name }}</p>
                                                                                    @endif
                                                                                </td>
                                                                                <td>{{ $p->patientsCQI ? $statusLabels[$p->patientsCQI->status] : 'Pending' }}</td>
                                                                                <td>{{ $p->created_at->format('m/d/Y') }}</td>
                                                                                <td><a href="{{ url('/users/submitted_cqi/' . $p->id) }}" class="btn btn-sm btn-primary">View CQI</a></td>
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                            @endif
                        @endif

                    </div>
                    @endforeach

                    {{-- ── Flagged Submissions tab pane ── --}}
                    <div class="tab-pane fade" id="content-flagged" role="tabpanel" aria-labelledby="tab-flagged">
                        @php
                            $fCurrent = $flaggedData['current'];
                            $fArchive = $flaggedData['archive'];
                        @endphp

                        @if($fCurrent->isEmpty() && empty($fArchive))
                            <div class="text-muted">No flagged submissions on record.</div>
                        @else
                            <p class="text-muted mb-3">
                                These patients answered <strong>Yes</strong> to one or more medical screening questions,
                                acknowledged the warning, then completed payment. A PDF audit record was saved for each.
                            </p>

                            {{-- Current month rows --}}
                            @if($fCurrent->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Name</th><th>Email</th><th>IP Address</th>
                                            <th>Acknowledged At</th><th>Triggered Questions</th>
                                            <th>Submitted On</th><th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fCurrent as $ack)
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

                            {{-- Archive folders --}}
                            @foreach($fArchive as $folder)
                                @if($folder['type'] === 'month')
                                    @php $folderId = 'arc_flagged_' . str_replace('-', '_', $folder['key']); @endphp
                                    <div class="mt-1">
                                        <button class="btn btn-light btn-sm w-100 text-start border"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $folderId }}"
                                                aria-expanded="false">
                                            &#x1F4C1; {{ $folder['label'] }} ({{ $folder['items']->count() }} {{ $folder['items']->count() === 1 ? 'submission' : 'submissions' }})
                                        </button>
                                        <div class="collapse" id="{{ $folderId }}">
                                            <div class="table-responsive ms-3 mt-1">
                                                <table class="table table-striped table-bordered table-sm align-middle">
                                                    <thead class="table-danger">
                                                        <tr><th>Name</th><th>Email</th><th>IP Address</th><th>Acknowledged At</th><th>Triggered Questions</th><th>Submitted On</th><th>Actions</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($folder['items'] as $ack)
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
                                        </div>
                                    </div>
                                @else
                                    @php $yearFolderId = 'arc_flagged_' . $folder['key']; @endphp
                                    <div class="mt-1">
                                        <button class="btn btn-secondary btn-sm w-100 text-start"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $yearFolderId }}"
                                                aria-expanded="false">
                                            &#x1F4C1; {{ $folder['label'] }} ({{ $folder['count'] }} {{ $folder['count'] === 1 ? 'submission' : 'submissions' }})
                                        </button>
                                        <div class="collapse" id="{{ $yearFolderId }}">
                                            <div class="ms-3 mt-1">
                                                @foreach($folder['months'] as $monthFolder)
                                                    @php $mFolderId = 'arc_flagged_' . str_replace('-', '_', $monthFolder['key']); @endphp
                                                    <div class="mt-1">
                                                        <button class="btn btn-light btn-sm w-100 text-start border"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#{{ $mFolderId }}"
                                                                aria-expanded="false">
                                                            &#x1F4C1; {{ $monthFolder['label'] }} ({{ $monthFolder['items']->count() }} {{ $monthFolder['items']->count() === 1 ? 'submission' : 'submissions' }})
                                                        </button>
                                                        <div class="collapse" id="{{ $mFolderId }}">
                                                            <div class="table-responsive ms-3 mt-1">
                                                                <table class="table table-striped table-bordered table-sm align-middle">
                                                                    <thead class="table-danger">
                                                                        <tr><th>Name</th><th>Email</th><th>IP Address</th><th>Acknowledged At</th><th>Triggered Questions</th><th>Submitted On</th><th>Actions</th></tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($monthFolder['items'] as $ack)
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
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                </div>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to approve this patient?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmApprove" class="btn btn-success">Approve</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Select a reason:</p>

                <div class="form-check">
                    <input class="form-check-input reject-reason" type="radio" name="reject_reason" value="ID verification failure">
                    <label class="form-check-label">ID verification failure</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input reject-reason" type="radio" name="reject_reason" value="Not clinically indicated">
                    <label class="form-check-label">Not clinically indicated</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input reject-reason" type="radio" name="reject_reason" value="Other">
                    <label class="form-check-label">Other</label>
                </div>

                <textarea class="form-control mt-2 d-none" id="otherReason" placeholder="Enter reason..."></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmReject" class="btn btn-danger">Reject</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.select2').select2();
        var base_url = $("body").data("base-url");

    });

    $(document).on('click', '.approve', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        $('#confirmApprove').attr('href', '/dashboard/approve_patient/' + id);
        $('#approveModal').modal('show');
    });

    let rejectPatientId = null;

    $(document).on('click', '.reject', function(e) {
        e.preventDefault();
        rejectPatientId = $(this).data('id');
        $('#rejectModal').modal('show');
    });

    $(document).on('click', '.approve_all', function(e) {
        e.preventDefault();

        $('#confirmApprove').attr('href', '/dashboard/approve_all_patients/');
        $('#approveModal').modal('show');
    });

    $(document).on('change', '.reject-reason', function() {
        if ($(this).val() === 'Other') {
            $('#otherReason').removeClass('d-none');
        } else {
            $('#otherReason').addClass('d-none').val('');
        }
    });

    $('#confirmReject').on('click', function() {
        let reason = $('input[name="reject_reason"]:checked').val();

        if (!reason) {
            alert('Please select a reason.');
            return;
        }

        if (reason === 'Other') {
            reason = $('#otherReason').val();
            if (!reason.trim()) {
                alert('Please enter a reason.');
                return;
            }
        }

        $.ajax({
            url: '/dashboard/reject_patient/' + rejectPatientId,
            type: 'POST',
            data: {
                reason: reason
            },
            success: function(response) {
                $('a.reject[data-id="' + rejectPatientId + '"]').closest('tr').remove();

                let rowHtml = '<tr>'
                    + '<td>' + response.patient.first_name + ' ' + response.patient.last_name + '</td>'
                    + '<td>' + (response.patient.artist_name || '') + '</td>'
                    + '<td>Rejected</td>'
                    + '<td>' + response.patient.submitted_on + '</td>'
                    + '<td><a href="/users/submitted_cqi/' + response.patient.id + '" class="btn btn-sm btn-primary">View CQI</a></td>'
                    + '</tr>';

                $('#content-2 tbody').prepend(rowHtml);
                $('#rejectModal').modal('hide');
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });
</script>
@endsection