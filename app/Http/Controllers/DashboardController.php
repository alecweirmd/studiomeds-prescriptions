<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientsCQI;
use App\Models\Patients;
use App\Models\PatientAcknowledgement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{

    public function index()
    {

        if (session()->get('user_type') == 1) {

            $data['patients'] = Patients::with('patientsCQI')
                ->get()
                ->whereNotNull('first_name')
                ->sortBy(fn($p) => $p->patientsCQI ? $p->patientsCQI->status : 0)
                ->groupBy(fn($p) => $p->patientsCQI ? $p->patientsCQI->status : 0);

            return view('dashboards/doctor', $data);
        } else {

            $data['clients'] = Auth::user()->patients;

            return view('dashboards/artist', $data);
        }
    }

    public function generate_QR()
    {
        $user = Auth::user();
        $url = url('/users/client_form/' . $user->uuid);

        // Generate QR as a PNG and encode it for browser display
        $qrPng = QrCode::format('png')->size(200)->generate($url);
        $base64 = base64_encode($qrPng);

        return response()->json([
            'status' => 'success',
            'qr' => 'data:image/png;base64,' . $base64,
            'shop' => $user->name_of_shop,
        ]);
    }

    public function generate_med_doc()
    {

        $pdf = Pdf::loadView('pdf/artist_medication', [
            'artist' => Auth::user()
        ]);

        return $pdf->stream('artist-medication-log.pdf');
    }

    public function approvePatient($id)
    {

        if (session()->get('user_type') != 1) {
            abort(404, 'Access denied.');
        }
        $patient = Patients::findOrFail($id);

        // Update status to approved
        $patient->patientsCQI->status = 1;
        $patient->patientsCQI->save();

        // Dispatch emails as background jobs
        \App\Jobs\SendPatientApprovalEmail::dispatch($patient->id);

        if ($patient->artist_id) {
            \App\Jobs\SendArtistApprovalEmail::dispatch($patient->id, $patient->artist_id);
        }

        Session::flash('type', 'success');
        Session::flash('message', 'Patient approved.');
        return redirect('/dashboard');
    }

    public function approveAllPatients()
    {
        // Get all patients awaiting approval
        $patients = Patients::with('patientsCQI')
            ->get()
            ->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 0);

        foreach ($patients as $patient) {
            $patient->patientsCQI->status = 1;
            $patient->patientsCQI->save();

            \App\Jobs\SendPatientApprovalEmail::dispatch($patient->id);

            if ($patient->artist_id) {
                \App\Jobs\SendArtistApprovalEmail::dispatch($patient->id, $patient->artist_id);
            }
        }

        Session::flash('type', 'success');
        Session::flash('message', 'Patients approved.');
        return redirect('/dashboard');
    }

    public function rejectPatient(Request $request, $id)
    {
        if (session()->get('user_type') != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // Find the patient
        $patient = Patients::findOrFail($id);

        // Update status + reason
        $patient->patientsCQI->status = 2;
        $patient->patientsCQI->rejection_reason = $request->reason;
        $patient->patientsCQI->save();

        // Reload relationships if needed
        $patient->load('patientsCQI', 'artist');

        // Dispatch rejection email as background job
        \App\Jobs\SendPatientRejectionEmail::dispatch($patient->id);

        return response()->json([
            'success' => true,
            'message' => 'Patient rejected.',
            'patient' => [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'artist_name' => $patient->artist->artist_name ?? $patient->artist_name,
                'status' => 'Rejected',
                'submitted_on' => $patient->created_at->format('m/d/Y')
            ]
        ]);
    }


    public function analytics()
    {
        if (session()->get('user_type') != 1) {
            abort(403);
        }

        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $allThisMonth = Patients::with('patientsCQI')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotNull('first_name')
            ->get();

        $total    = $allThisMonth->count();
        $approved = $allThisMonth->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 1)->count();
        $rejected = $allThisMonth->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 2)->count();

        $revenue        = $approved * 35.00;
        $approvalRate   = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
        $rejectionRate  = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        $cityBreakdown = Patients::whereNotNull('city')
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->pluck('count', 'city');

        return view('dashboards/analytics', compact(
            'total', 'approved', 'rejected', 'revenue',
            'approvalRate', 'rejectionRate', 'cityBreakdown'
        ));
    }

    public function training()
    {

        return view('info/training');
    }

    public function flaggedSubmissions()
    {
        if (session()->get('user_type') != 1) {
            abort(403);
        }

        $questionLabels = [
            'lidocaine'         => 'Q1: Allergic reaction to numbing creams/anesthetics',
            'bactine'           => 'Q2: Allergic reaction to Bactine/antiseptics',
            'broken_skin'       => 'Q3: Broken skin or open wounds',
            'eczema'            => 'Q4: Severe eczema, psoriasis, or skin conditions',
            'heart_rhythm'      => 'Q5: Heart rhythm problems',
            'liver_disease'     => 'Q6: Severe liver disease',
            'seizures'          => 'Q7: Seizures from medications/anesthetics',
            'pregnant'          => 'Q8: Pregnant or breastfeeding',
            'antiarrhythmic'    => 'Q9: Medications for irregular heartbeat',
            'seizure_meds'      => 'Q10: Seizure/nerve pain medications',
            'fainted'           => 'Q11: Fainted or severe reaction to anesthetics',
            'methemoglobinemia' => 'Q12: Methemoglobinemia or blood oxygen disorder',
        ];

        $flagged = PatientAcknowledgement::with(['patient.patientsCQI'])
            ->whereNotNull('patient_id')
            ->whereNotNull('pdf_path')
            ->latest('acknowledged_at')
            ->get();

        return view('dashboards/flagged_submissions', compact('flagged', 'questionLabels'));
    }

    public function downloadFlaggedPdf($id)
    {
        if (session()->get('user_type') != 1) {
            abort(403);
        }

        $ack = PatientAcknowledgement::findOrFail($id);

        if (!$ack->pdf_path || !Storage::exists('flagged_submissions/' . $ack->pdf_path)) {
            abort(404, 'PDF not found.');
        }

        return response(Storage::get('flagged_submissions/' . $ack->pdf_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $ack->pdf_path . '"',
        ]);
    }
}
