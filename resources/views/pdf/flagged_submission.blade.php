<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; color: #c0392b; border-bottom: 2px solid #c0392b; padding-bottom: 6px; }
        h2 { font-size: 14px; margin-top: 20px; border-bottom: 1px solid #aaa; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f2f2f2; text-align: left; padding: 6px 8px; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .flag-box { background: #fdf2f2; border: 1px solid #e74c3c; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }
        .yes { color: #c0392b; font-weight: bold; }
        .no  { color: #27ae60; }
    </style>
</head>
<body>

<h1>Flagged Submission — Legal Audit Record</h1>

<div class="flag-box">
    <strong>Patient IP Address:</strong> {{ $acknowledgement->ip_address }}<br>
    <strong>Acknowledgement Timestamp:</strong> {{ $acknowledgement->acknowledged_at->format('F j, Y g:i:s A') }} UTC<br>
    <strong>Session ID:</strong> {{ $acknowledgement->session_id }}<br>
    <strong>Questions that triggered the warning:</strong>
    {{ implode(', ', array_map(fn($q) => $questionLabels[$q] ?? $q, $acknowledgement->triggered_questions)) }}
</div>

<h2>Patient Information</h2>
<table>
    <tr><th>Name</th><td>{{ $patient->first_name }} {{ $patient->last_name }}</td></tr>
    <tr><th>Date of Birth</th><td>{{ $patient->date_of_birth }}</td></tr>
    <tr><th>Email</th><td>{{ $patient->email }}</td></tr>
    <tr><th>Address</th><td>{{ $patient->street_address }}, {{ $patient->city }}, {{ $patient->state }} {{ $patient->zip }}</td></tr>
    <tr><th>Submission Date</th><td>{{ $patient->created_at->format('F j, Y g:i:s A') }} UTC</td></tr>
</table>

<h2>Medical Screening Answers</h2>
<table>
    <thead>
        <tr><th>#</th><th>Question</th><th>Answer</th></tr>
    </thead>
    <tbody>
        @foreach($questionLabels as $field => $label)
        @if($answers->$field !== null)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $label }}</td>
            <td class="{{ $answers->$field ? 'yes' : 'no' }}">{{ $answers->$field ? 'YES' : 'No' }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>

<h2>Acknowledgement Detail</h2>
<p>
    The patient was shown the following warning and clicked <strong>"I Understand"</strong> before proceeding to payment:
</p>
<blockquote style="border-left:3px solid #e74c3c;padding-left:10px;color:#555;">
    "Given your medical history it is recommended you see an in person provider to receive a prescription for topical anesthetics."
</blockquote>
<p>
    After acknowledging this warning, the patient completed the intake form and payment.
    This PDF was automatically generated as a legal audit record at the time of payment.
</p>

</body>
</html>
