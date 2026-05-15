@extends('layouts.app')

@section('content')

<div class="row gy-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Marketing</h2>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">Clinical Dashboard</a>
                    <a href="{{ url('/dashboard/analytics') }}" class="btn btn-secondary btn-sm">Analytics</a>
                    <a href="{{ url('/dashboard/sponsorship') }}" class="btn btn-secondary btn-sm">
                        &#x1F4DD; Sponsorship
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Left sidebar --}}
                    <div class="col-md-3 col-lg-2">
                        <div class="nav flex-column nav-pills" id="marketingSidebar" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active text-start mb-1" id="tab-utm" data-bs-toggle="pill"
                                    data-bs-target="#pane-utm" type="button" role="tab">
                                &#x1F4C8; UTM Overview
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-link" data-bs-toggle="pill"
                                    data-bs-target="#pane-link" type="button" role="tab">
                                &#x1F517; Link Builder
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-qr" data-bs-toggle="pill"
                                    data-bs-target="#pane-qr" type="button" role="tab">
                                &#x25A3; QR Code Generator
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-codes" data-bs-toggle="pill"
                                    data-bs-target="#pane-codes" type="button" role="tab">
                                &#x1F39F; Referral Codes
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-metrics" data-bs-toggle="pill"
                                    data-bs-target="#pane-metrics" type="button" role="tab">
                                &#x1F4CA; Referral Code Metrics
                            </button>
                            <button class="nav-link text-start mb-1" id="tab-test-emails" data-bs-toggle="pill"
                                    data-bs-target="#pane-test-emails" type="button" role="tab">
                                &#x2709; Test Emails
                            </button>
                        </div>
                    </div>

                    {{-- Main pane --}}
                    <div class="col-md-9 col-lg-10">
                        <div class="tab-content" id="marketingContent">

                            {{-- UTM Overview --}}
                            <div class="tab-pane fade show active" id="pane-utm" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">UTM Overview</h5>
                                    <div class="btn-group" role="group" aria-label="Time range">
                                        <a href="{{ url('/dashboard/marketing?range=7') }}#tab-utm"
                                           class="btn btn-sm {{ $utmRange === '7' ? 'btn-primary' : 'btn-outline-primary' }}">Last 7 Days</a>
                                        <a href="{{ url('/dashboard/marketing?range=30') }}#tab-utm"
                                           class="btn btn-sm {{ $utmRange === '30' ? 'btn-primary' : 'btn-outline-primary' }}">Last 30 Days</a>
                                        <a href="{{ url('/dashboard/marketing?range=all') }}#tab-utm"
                                           class="btn btn-sm {{ $utmRange === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All Time</a>
                                    </div>
                                </div>

                                @if(empty($utmSources))
                                    <div class="text-muted">No UTM visits captured yet for this range.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Source</th>
                                                    <th class="text-end">Visits</th>
                                                    <th class="text-end">Completed</th>
                                                    <th class="text-end">Conversion Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($utmSources as $row)
                                                    <tr>
                                                        <td>{{ $row['source'] }}</td>
                                                        <td class="text-end">{{ $row['visits'] }}</td>
                                                        <td class="text-end">{{ $row['completed'] }}</td>
                                                        <td class="text-end">{{ $row['conversion_rate'] }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td>Total</td>
                                                    <td class="text-end">{{ $utmTotals['visits'] }}</td>
                                                    <td class="text-end">{{ $utmTotals['completed'] }}</td>
                                                    <td class="text-end">{{ $utmTotals['conversion_rate'] }}%</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Link Builder --}}
                            <div class="tab-pane fade" id="pane-link" role="tabpanel">
                                <h5 class="mb-3">Link Builder</h5>
                                <div class="row g-3" style="max-width:640px;">
                                    <div class="col-md-6">
                                        <label class="form-label">Source</label>
                                        <select class="form-select" id="linkSource">
                                            <option value="facebook">Facebook</option>
                                            <option value="postcard">Postcard</option>
                                            <option value="business_card">Business Card</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="tiktok">TikTok</option>
                                            <option value="convention">Convention</option>
                                            <option value="email">Email</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Campaign (optional)</label>
                                        <input type="text" class="form-control" id="linkCampaign" placeholder="e.g. spring2026">
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2 align-items-center" style="max-width:640px;">
                                    <input type="text" class="form-control" id="linkOutput" readonly>
                                    <button class="btn btn-primary" id="copyLinkBtn">Copy</button>
                                </div>
                                <div id="copyLinkConfirm" class="text-success small mt-1" style="display:none;">Copied!</div>
                            </div>

                            {{-- QR Code Generator --}}
                            <div class="tab-pane fade" id="pane-qr" role="tabpanel">
                                <h5 class="mb-3">QR Code Generator</h5>
                                <div class="row g-3" style="max-width:640px;">
                                    <div class="col-md-6">
                                        <label class="form-label">Destination</label>
                                        <select class="form-select" id="qrPreset">
                                            <option value="homepage" selected>Homepage</option>
                                            <option value="prescription">Prescription site</option>
                                            <option value="tattoo">Tattoo page</option>
                                            <option value="pmu">PMU page</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-primary" id="qrGenerateBtn">Generate</button>
                                    <a class="btn btn-success" id="qrDownloadBtn" style="display:none;" download="studiomeds-qr.svg">Download SVG</a>
                                </div>
                                <div class="mt-3" id="qrPreview" style="display:none;">
                                    <img id="qrImage" alt="QR Code" style="background:#fff;border:1px solid #dee2e6;padding:8px;max-width:240px;">
                                    <div class="text-muted small mt-2"><code id="qrUrl"></code></div>
                                </div>
                            </div>

                            {{-- Referral Codes --}}
                            <div class="tab-pane fade" id="pane-codes" role="tabpanel">
                                <h5 class="mb-3">Create Referral Code</h5>
                                <form method="POST" action="{{ url('/dashboard/marketing/codes') }}" class="mb-4" style="max-width:880px;">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Code String</label>
                                            <input type="text" class="form-control" name="code_string" required maxlength="60" value="{{ old('code_string') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Partner Name</label>
                                            <input type="text" class="form-control" name="partner_name" required maxlength="255" value="{{ old('partner_name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Discount Type</label>
                                            <select class="form-select" name="discount_type" id="newCodeType" required>
                                                <option value="free"             {{ old('discount_type') === 'free' ? 'selected' : '' }}>Free (100% off)</option>
                                                <option value="fixed_dollar_off" {{ old('discount_type') === 'fixed_dollar_off' ? 'selected' : '' }}>Fixed Dollar Off</option>
                                                <option value="percent_off"      {{ old('discount_type') === 'percent_off' ? 'selected' : '' }}>Percent Off</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3" id="newCodeValueWrap" style="{{ old('discount_type', 'free') === 'free' ? 'display:none;' : '' }}">
                                            <label class="form-label">Discount Value</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="discount_value" value="{{ old('discount_value') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Usage Cap</label>
                                            <input type="number" min="1" class="form-control" name="usage_cap" required value="{{ old('usage_cap', 1) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Expiration Date</label>
                                            <input type="date" class="form-control" name="expiration_date" required value="{{ old('expiration_date') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" name="notes" rows="2" maxlength="5000">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">Create Code</button>
                                    </div>
                                </form>

                                <h5 class="mb-3">All Referral Codes</h5>
                                @if($codes->isEmpty())
                                    <div class="text-muted">No referral codes created yet.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Partner</th>
                                                    <th>Type</th>
                                                    <th>Usage</th>
                                                    <th>Expiration</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($codes as $c)
                                                    <tr data-code-id="{{ $c->id }}">
                                                        <td><code>{{ $c->code_string }}</code></td>
                                                        <td>{{ $c->partner_name }}</td>
                                                        <td>
                                                            @if($c->discount_type === 'free') Free
                                                            @elseif($c->discount_type === 'fixed_dollar_off') ${{ number_format((float)$c->discount_value, 2) }} off
                                                            @else {{ rtrim(rtrim(number_format((float)$c->discount_value, 2), '0'), '.') }}% off
                                                            @endif
                                                        </td>
                                                        <td>{{ $c->usage_count }} / {{ $c->usage_cap }}</td>
                                                        <td>{{ $c->expiration_date ? $c->expiration_date->format('m/d/Y') : '—' }}</td>
                                                        <td class="status-cell">
                                                            @php
                                                                $statusBadge = [
                                                                    'active'    => 'success',
                                                                    'exhausted' => 'danger',
                                                                    'expired'   => 'secondary',
                                                                    'paused'    => 'warning',
                                                                ][$c->status] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge bg-{{ $statusBadge }}">{{ ucfirst($c->status) }}</span>
                                                        </td>
                                                        <td>
                                                            @if(in_array($c->status, ['active', 'paused']))
                                                                <button type="button"
                                                                        class="btn btn-sm {{ $c->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-code-btn"
                                                                        data-code-id="{{ $c->id }}">
                                                                    {{ $c->status === 'active' ? 'Pause' : 'Activate' }}
                                                                </button>
                                                            @else
                                                                <span class="text-muted small">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Referral Code Metrics --}}
                            <div class="tab-pane fade" id="pane-metrics" role="tabpanel">
                                <h5 class="mb-3">Referral Code Metrics</h5>
                                @if($metrics->isEmpty())
                                    <div class="text-muted">No referral codes created yet.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered align-middle" id="metricsTable">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Partner</th>
                                                    <th class="text-end">Total Uses</th>
                                                    <th class="text-end">Usage Cap</th>
                                                    <th class="text-end">Remaining</th>
                                                    <th>Expiration</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Total Value Comped</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($metrics as $m)
                                                    <tr>
                                                        <td><code>{{ $m['code_string'] }}</code></td>
                                                        <td>{{ $m['partner_name'] }}</td>
                                                        <td class="text-end">{{ $m['usage_count'] }}</td>
                                                        <td class="text-end">{{ $m['usage_cap'] }}</td>
                                                        <td class="text-end">{{ $m['remaining'] }}</td>
                                                        <td data-order="{{ $m['expiration_date'] ? $m['expiration_date']->format('Y-m-d') : '' }}">
                                                            {{ $m['expiration_date'] ? $m['expiration_date']->format('m/d/Y') : '—' }}
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusBadge = [
                                                                    'active'    => 'success',
                                                                    'exhausted' => 'danger',
                                                                    'expired'   => 'secondary',
                                                                    'paused'    => 'warning',
                                                                ][$m['status']] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge bg-{{ $statusBadge }}">{{ ucfirst($m['status']) }}</span>
                                                        </td>
                                                        <td class="text-end">${{ number_format($m['total_value_comped'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Test Emails --}}
                            <div class="tab-pane fade" id="pane-test-emails" role="tabpanel">
                                <h5 class="mb-3">Test Emails</h5>
                                <p class="text-muted">All test emails are sent to <code>alecweir@gmail.com</code>.</p>

                                <div id="testEmailStatus" class="mb-3"></div>

                                <div class="row g-3" style="max-width:880px;">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">Abandoned Intake Outreach</h6>
                                                <p class="card-text text-muted small">Sent to patients who started an intake but did not complete it.</p>
                                                <button type="button" class="btn btn-primary test-email-btn" data-email-type="abandoned_intake">
                                                    Send Test: Abandoned Intake
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">3-Day Post-Approval Follow-up</h6>
                                                <p class="card-text text-muted small">Sent 3 days after a patient is approved.</p>
                                                <button type="button" class="btn btn-primary test-email-btn" data-email-type="post_approval_followup">
                                                    Send Test: Post-Approval Follow-up
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">7-Day Facebook Review Request</h6>
                                                <p class="card-text text-muted small">Sent 7 days after approval requesting a Facebook review.</p>
                                                <button type="button" class="btn btn-primary test-email-btn" data-email-type="facebook_review_request">
                                                    Send Test: Facebook Review Request
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">90-Day Rejection Re-engagement</h6>
                                                <p class="card-text text-muted small">Sent 90 days after a rejection inviting another evaluation.</p>
                                                <button type="button" class="btn btn-primary test-email-btn" data-email-type="rejection_reengagement">
                                                    Send Test: Rejection Re-engagement
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    var BASE = '{{ $baseUrl }}';

    // Activate tab from hash on load (e.g. after redirect with #tab-codes)
    if (window.location.hash) {
        var hash = window.location.hash;
        var btn = document.querySelector('[data-bs-target="#pane-' + hash.replace('#tab-', '').replace('#pane-', '') + '"]');
        if (btn) {
            try { new bootstrap.Tab(btn).show(); } catch (e) {}
        }
    }

    // ── Link Builder ────────────────────────────────────────────────────
    function buildLinkUrl(source, campaign) {
        var params = new URLSearchParams();
        if (source) { params.set('utm_source', source); }
        if (campaign) { params.set('utm_campaign', campaign); }
        var qs = params.toString();
        return BASE + (qs ? ('?' + qs) : '');
    }
    function refreshLink() {
        var src = $('#linkSource').val();
        var camp = $('#linkCampaign').val().trim();
        $('#linkOutput').val(buildLinkUrl(src, camp));
    }
    $('#linkSource, #linkCampaign').on('input change', refreshLink);
    refreshLink();
    $('#copyLinkBtn').on('click', function() {
        var input = document.getElementById('linkOutput');
        input.select(); input.setSelectionRange(0, 99999);
        try {
            navigator.clipboard.writeText(input.value);
            $('#copyLinkConfirm').show();
            setTimeout(function() { $('#copyLinkConfirm').fadeOut(); }, 1500);
        } catch (e) {
            document.execCommand('copy');
        }
    });

    // ── QR Generator ────────────────────────────────────────────────────
    $('#qrGenerateBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        $.ajax({
            url: '/dashboard/marketing/qr',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                preset: $('#qrPreset').val(),
            },
        }).done(function(resp) {
            if (resp && resp.qr) {
                $('#qrImage').attr('src', resp.qr);
                $('#qrUrl').text(resp.url);
                $('#qrPreview').show();
                $('#qrDownloadBtn').attr('href', resp.qr).show();
            } else {
                alert('Could not generate QR code.');
            }
        }).fail(function() {
            alert('Could not generate QR code.');
        }).always(function() {
            $btn.prop('disabled', false).text('Generate');
        });
    });

    // ── New code form: hide value field for "free" type ────────────────
    $('#newCodeType').on('change', function() {
        if ($(this).val() === 'free') {
            $('#newCodeValueWrap').hide();
            $('#newCodeValueWrap input').val('');
        } else {
            $('#newCodeValueWrap').show();
        }
    });

    // ── Pause / Activate referral code ─────────────────────────────────
    $(document).on('click', '.toggle-code-btn', function() {
        var $btn = $(this);
        var codeId = $btn.data('code-id');
        var $row = $btn.closest('tr');
        var $statusCell = $row.find('.status-cell');
        var prevBtnHtml = $btn.html();
        $btn.prop('disabled', true).text('…');

        $.ajax({
            url: '/dashboard/marketing/toggle-code/' + codeId,
            type: 'POST',
            dataType: 'json',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
        }).done(function(resp) {
            if (resp && resp.success) {
                var newStatus = resp.status;
                var label = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                var badgeClass = newStatus === 'active' ? 'bg-success' : 'bg-warning';
                $statusCell.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
                if (newStatus === 'active') {
                    $btn.removeClass('btn-outline-success').addClass('btn-outline-warning').text('Pause');
                } else {
                    $btn.removeClass('btn-outline-warning').addClass('btn-outline-success').text('Activate');
                }
                $btn.prop('disabled', false);
            } else {
                $btn.prop('disabled', false).html(prevBtnHtml);
                alert((resp && resp.error) ? resp.error : 'Could not toggle code.');
            }
        }).fail(function() {
            $btn.prop('disabled', false).html(prevBtnHtml);
            alert('Could not toggle code. Please try again.');
        });
    });

    // ── Test Emails ────────────────────────────────────────────────────
    $(document).on('click', '.test-email-btn', function() {
        var $btn = $(this);
        var type = $btn.data('email-type');
        var prevText = $btn.text();
        var $status = $('#testEmailStatus');
        $status.empty();
        $btn.prop('disabled', true).text('Sending…');

        $.ajax({
            url: '/dashboard/marketing/test-email',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                type:   type,
            },
        }).done(function(resp) {
            if (resp && resp.success) {
                $status.html('<div class="alert alert-success mb-0">' + (resp.message || 'Test email sent.') + '</div>');
            } else {
                $status.html('<div class="alert alert-danger mb-0">' + ((resp && resp.error) ? resp.error : 'Could not send test email.') + '</div>');
            }
        }).fail(function(xhr) {
            var msg = 'Could not send test email.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $status.html('<div class="alert alert-danger mb-0">' + msg + '</div>');
        }).always(function() {
            $btn.prop('disabled', false).text(prevText);
        });
    });

    // ── Metrics table — sortable ───────────────────────────────────────
    if (document.getElementById('metricsTable')) {
        $('#metricsTable').DataTable({
            paging: false,
            info: false,
            searching: false,
            order: [[2, 'desc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });
    }
});
</script>
@endsection
