@extends('layouts.app')

@section('content')

<div class="row gy-4">

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2>Submitted Client Forms</h2>
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
                </ul>

                <div class="tab-content p-3 border border-top-0" id="patientTabsContent">
                    @foreach($statusLabels as $status => $label)
                    <div class="tab-pane fade @if($loop->first) show active @endif"
                        id="content-{{ $status }}"
                        role="tabpanel"
                        aria-labelledby="tab-{{ $status }}">

                        @php
                        $group = $patients[$status] ?? collect();
                        @endphp

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
                                        <th>Actions @if($status == 0)<span class="text-end"><a class="btn btn-sm btn-success approve_all">Approve All</a></span>@endif</th>
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
                                        <td>{{ $statusLabels[$p->patientsCQI->status] }}</td>
                                        <td>{{ $p->created_at->format('m/d/Y') }}</td>
                                        <td>
                                            <a href="{{ url('/users/submitted_cqi/'); }}/{{$p->id}}" class="btn btn-sm btn-primary">
                                                View CQI
                                            </a>
                                            @if($p->patientsCQI->status == 0)
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

                    </div>
                    @endforeach
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
                $('#content-2 tbody').prepend(response.row_html);

                let rowHtml = '<tr><td>'+ response.patient.first_name + ' ' +  response.patient.last_name +'</td><td>'+ response.patient.artist_name +'</td><td>'+ response.patient.status +'</td><td>'+ response.patient.submitted_on +'</td><td><a href="/users/submitted_cqi/'+ response.patient.id +'" class="btn btn-sm btn-primary">View CQI</a></td></tr>';

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