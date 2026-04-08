<p>Hello {{ $patient->first_name }},</p>

<p>Your prescriptions could not be approved via studio meds for the following reason:</p> 
<p>{{ $patient->patientsCQI ? $patient->patientsCQI->rejection_reason : '' }}</p>
<p>This is an automated message. For support or questions, please contact us at <a href="mailto:admin@studiomeds.com">admin@studiomeds.com</a> so our team can assist you promptly.</p>

<p>Thank you,</br>
StudioMeds, PLLC</br>
<a href="mailto:admin@studiomeds.com">admin@studiomeds.com</a></br>
<a href="www.studiomeds.com">www.studiomeds.com</a></br>
</p>