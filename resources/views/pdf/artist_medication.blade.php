<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Artist Medication Log</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 14px;
                line-height: 1.5;
                margin: 40px;
            }
            h1 {
                text-align: center;
                margin-bottom: 30px;
                text-decoration: underline;
            }
            .field {
                margin-bottom: 18px;
            }
            .label {
                font-weight: bold;
            }
            .line {
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 400px;
                height: 18px;
                vertical-align: bottom;
            }
            .small-line {
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 250px;
                height: 18px;
                vertical-align: bottom;
            }
            .section-title {
                font-weight: bold;
                margin-top: 25px;
            }
        </style>
    </head>
    <body>    
        <div style="text-align:center; margin-bottom:20px;">
            <img src="{{ public_path('images/Studiomeds_cropped.png') }}" width="250">
        </div>

        <h1>Topical Anesthetic Application Log</h1>

        <div class="field">
            <span class="label">Artist Name: &nbsp;@if($artist->artist_name != NULL)<p>{{ $artist->artist_name }}</p>@else<p>{{ $artist->first_name }} {{ $artist->last_name }}</p>@endif
            </span>
        </div>

        <div class="field">
            <span class="label">Client Name:</span>
            <span class="line"></span>
        </div>

        <div class="field">
            <span class="label">Date of Procedure:</span>
            <span class="small-line"></span>
        </div>

        <div class="field">
            <span class="label">Lidocaine 5% - Amount Used (if any):</span>
            <span class="small-line"></span>
        </div>

        <div class="field">
            <span class="label">Bactine Max Spray - Amount Used (if any):</span>
            <span class="small-line"></span>
        </div>

        <div class="field">
            <span class="label">Any Adverse Effects/Events? (Yes/No):</span>
            <span class="small-line"></span>
        </div>

        <div style="margin-left:20px; margin-top:10px;">
            If yes: describe and what actions were taken:<br><br>
            <div class="line" style="width:100%; height:40px;"></div>
        </div>

        <br><br>

        <div class="field">
            <span class="label">Artist’s Signature:</span>
            <span class="small-line"></span>
        </div>

        <p style="margin-top:40px; font-size:12px;">
            <strong>IMPORTANT:</strong> If any adverse events are recorded, Studiomeds must be notified immediately via phone at (989) 272-3451.
        </p>

    </body>
</html>
