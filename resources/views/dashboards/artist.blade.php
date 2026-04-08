@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">  
                <h2>Quick Links</h2>     
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <tr>
                        <td>
                            <button class='qrgen' href="{{url('/generate_QR')}}">Generate QR Code</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="{{url('/pdf/artist_medication')}}">Generate Medication PDF</a>
                        </td>
                    </tr>
                    <tr>
                        <td> 
                            <a href="{{url('/dashboard/training')}}">Training Materials</a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
 
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">  
                <h2>Submitted Client Forms</h2>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <th>
                            <p>Client Name</p>
                        </th>
                        <th>
                            <p>Status</p>
                        </th>
                        <th>
                            <p>Actions</p>
                        </th>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                            <tr>
                                <td>{{ $client->first_name }} {{ $client->last_name }}</td>
                                <td>
                                    @if($client->status == 0)
                                    Submitted
                                    @elseif($client->status == 1)
                                    Approved
                                    @elseif($client->status == 2)
                                    Rejected
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-success" href="{{ url('/users/submitted_cqi/'); }}/{{$client->id}}">View Submitted CQI</a>
                                    <button class="btn btn-sm btn-primary upload-file-btn" data-patient-id="{{ $client->id }}">Upload Medication PDF</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="QRModal" tabindex="-1" role="dialog" aria-labelledby="CommentModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">QR Code for {{Auth::user()->name_of_shop}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class='row'>
                    <div class='col QR'>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success save_comments">Save</button>
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

    $(document).ready(function () {
        $('.select2').select2();

        var base_url = $("body").data("base-url");

        $('.qrgen').on('click', function () {
            $.ajax({
                url: base_url + '/generate_QR',
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    // Inject the QR image
                    $('.QR').html(`
                        <img src="${result.qr}" alt="QR Code" class="img-fluid mb-2"><br/>
                        <a href="${result.qr}" download="qr-code.png" class="btn btn-sm btn-primary">Download QR</a>
                    `);
                    $('#QRModal').modal('show');
                } // success
            }); // ajax
        });
    });
</script>
@endsection