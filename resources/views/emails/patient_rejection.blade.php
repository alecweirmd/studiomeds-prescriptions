<p>Hello {{ $patient->first_name }},</p>

<p>Unfortunately your prescription could not be approved via StudioMeds for the following reason:</p>
<p>{{ $patient->patientsCQI ? $patient->patientsCQI->rejection_reason : '' }}</p>
<p>You may be a better candidate for an in person evaluation for anesthetic medications. For support or questions, please contact us at <a href="mailto:admin@studiomeds.com">admin@studiomeds.com</a> so our team can assist you promptly.</p>

<p>Thank you,</br>
StudioMeds, PLLC</br>
<a href="mailto:admin@studiomeds.com">admin@studiomeds.com</a></br>
<a href="http://www.studiomeds.com">www.studiomeds.com</a></br>
</p>
