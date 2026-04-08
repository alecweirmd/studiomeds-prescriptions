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


        <h4>Prescription: Bactine Max Spray (Lidocaine 4%)</h4>


        <p><strong>Patient Name:</strong> {{$patient->first_name}} {{$patient->last_name}}</p>
        <p><strong>Date of Birth:</strong> {{date('m/d/Y', strtotime($patient->date_of_birth))}}</p>
        <p><strong>Date:</strong> {{date('m/d/Y')}}</p>

        <p><strong>Medication:</strong> Bactine Max Spray or generic equivalent (Lidocaine 4%)</p>
        
        <p><strong>Active:</strong> Lidocaine HCl 4%</p>
        <p><strong>InActive:</strong> Benzalkonium chloride 0.13% (antiseptic), Poloxamer 188, Edetate disodium, Citric acid, Sodium citrate, Purified water</p>

        <p><strong>Sig:</strong> Apply a thin layer of medication to the affected area of skin. Do not apply more than 4 sprays twice in a given session.</p>
        <p><strong>Dispense:</strong> 5 fluid ounces (1 bottle)</p>
        <p><strong>Refills:</strong> None</p>


        <h5>Prescriber:</h5>
        <img src="{{ public_path('images/signature.jpg') }}" alt="StudioMeds Logo" height="80">
        <p>Alec Weir, MD<br>(989) 272-3451<br>NPI: 1346683232</p>
    </div>
</body>

</html>