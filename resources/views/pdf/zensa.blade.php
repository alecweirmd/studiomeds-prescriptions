<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Prescription</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="text-align:center; margin-bottom:20px;">
            <img src="{{ public_path('images/Studiomeds_cropped.png') }}" width="250">
        </div>


        <h4>Prescription: Zensa Numbing Cream (Lidocaine 5% topical cream)</h4>


        <p>Patient Name: {{$patient->first_name}} {{$patient->last_name}}</p>
        <p>Date of Birth: {{date('m/d/Y', strtotime($patient->date_of_birth))}}</p>
        <p>Date: {{date('m/d/Y')}}</p>

        <p><strong>Medication:</strong> Zensa Numbing Cream (Lidocaine 5% topical cream)</p>
        <p>NDC: 69805-786-01</p>
        <p>Manufacturer: Alera Skin Care Products Inc.</p>

        <p><strong>Active:</strong> Lidocaine 5%</p>
        <p><strong>Inactive:</strong> benzyl alcohol, carbopol, lecithin, propylene glycol, vitamin E acetate, purified water</p>

        <p><strong>Sig:</strong> Apply to skin in procedure area prior to procedure. May reapply on broken skin during procedure as needed. Do not apply more than 4 g total across the entire procedure. Do not occlude. Do not combine with other topical anesthetics.</p>
        <p><strong>Dispense:</strong> 30 g (1 tube)</p>
        <p><strong>Refills:</strong> None</p>


        <h5>Prescriber:</h5>
        <img src="{{ public_path('images/signature.jpg') }}" alt="StudioMeds Logo" height="80">
        <p>Alec Weir, MD<br>(989) 272-3451<br>NPI: 1346683232</p>
    </div>
</body>

</html>
