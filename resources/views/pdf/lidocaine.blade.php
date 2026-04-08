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


        <h4>Prescription: Aspercreme 4% lidocaine cream</h4>


        <p><strong>Patient Name:</strong> {{$patient->first_name}} {{$patient->last_name}}</p>
        <p><strong>Date of Birth:</strong> {{date('m/d/Y', strtotime($patient->date_of_birth))}}</p>
        <p><strong>Date:</strong> {{date('m/d/Y')}}</p>


        <p><strong>Medication:</strong> Aspercreme or generic equivalent (Lidocaine 4% topical ointment)</p>
        <p><strong>Active:</strong> Lidocaine HCl 4% </p>
        <p><strong>Inactive:</strong> acrylates/C10-30 alkyl acrylate crosspolymer, alcohol denat. (15%), aloe barbadensis leaf juice, aminomethyl propanol, C30-45 alkyl cetearyl dimethicone crosspolymer, caprylyl methicone, cetearyl alcohol, ceteth-20 phosphate, dicetyl phosphate, dimethicone, disodium EDTA, ethylhexylglycerin, glyceryl stearate, methylparaben, steareth-21, water</p>

        <p><strong>Sig:</strong> Apply to unbroken skin prior to procedure. Do not apply more than 10 g in one sitting.</p>
        <p><strong>Dispense:</strong> 49.6 g (1 tube) </p>
        <p><strong>Refills:</strong> None</p>


        <h5>Prescriber:</h5>
        <img src="{{ public_path('images/signature.jpg') }}" alt="StudioMeds Logo" height="80">
        <p>Alec Weir, MD<br>(989) 272-3451<br>NPI: 1346683232</p>
    </div>
</body>

</html>