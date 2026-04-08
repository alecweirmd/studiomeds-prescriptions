<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientsCQI;
use App\Models\Patients;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{

    public function index()
    {

        if (session()->get('user_type') == 1) {

            $data['patients'] = Patients::with('patientsCQI')   // load CQI
                ->get()
                ->whereNotNull('first_name')
                ->sortBy(fn($p) => $p->patientsCQI->status ?? 0)        // sort by status (0,1,2)
                ->groupBy(fn($p) => $p->patientsCQI->status ?? 0);

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
        //Find the patient
        $patient = Patients::findOrFail($id);
        if ($patient->artist_id) {
            $artist = User::findOrFail($patient->artist_id);
        } else {
            $artist = $patient->artist_name;
        }

        ///Update status to approved (1)
        $patient->patientsCQI->status = 1;
        $patient->patientsCQI->save();

        // Generate PDFs using the prescription blades
        $pdf1 = PDF::loadView('pdf/bactine', ['patient' => $patient]);
        $pdf2 = PDF::loadView('pdf/lidocaine', ['patient' => $patient]);

        // Create file paths
        $file1 = storage_path("app/bactine_{$patient->id}.pdf");
        $file2 = storage_path("app/lidocaine_{$patient->id}.pdf");

        // Save the PDFs temporarily
        $pdf1->save($file1);
        $pdf2->save($file2);

        // 4. Send the email with attachments
        Mail::send('emails/patient_approved', ['patient' => $patient], function ($message) use ($patient, $artist, $file1, $file2) {
            $message->to($patient->email)
                ->subject('Your Medications Are Approved')
                ->attach($file1, ['as' => 'Bactine.pdf'])
                ->attach($file2, ['as' => 'Preparation H.pdf']);
        });

        if ($patient->artist_id) {
            Mail::send('emails/artist_approved', ['patient' => $patient, 'artist' => $artist], function ($message) use ($patient, $artist, $file1, $file2) {
                $message->to($artist->email)
                    ->subject('Your Medications Are Approved')
                    ->attach($file1, ['as' => 'Bactine.pdf'])
                    ->attach($file2, ['as' => 'Preparation H.pdf']);
            });
        }

        // Optional: delete temp files after sending
        unlink($file1);
        unlink($file2);

        Session::flash('type', 'success');
        Session::flash('message', 'Patient approved.');
        return redirect('/dashboard');
    }

    public function approveAllPatients()
    {
        // Get all patients awaiting approval
        $patients = Patients::with('patientsCQI')
            ->get()
            ->filter(fn($p) => ($p->patientsCQI->status ?? 0) == 0);

        foreach ($patients as $patient) {

            try {
                // Get artist relationship or fallback
                if ($patient->artist_id) {
                    $artist = User::find($patient->artist_id);  // safe (find, not findOrFail)
                } else {
                    $artist = null; // No artist object
                }

                // Generate PDFs
                $pdf1 = PDF::loadView('pdf/bactine', ['patient' => $patient]);
                $pdf2 = PDF::loadView('pdf/lidocaine', ['patient' => $patient]);

                $file1 = storage_path("app/bactine_{$patient->id}.pdf");
                $file2 = storage_path("app/lidocaine_{$patient->id}.pdf");

                $pdf1->save($file1);
                $pdf2->save($file2);

                // Send email to patient
                Mail::send('emails/patient_approved', ['patient' => $patient], function ($message) use ($patient, $file1, $file2) {
                    $message->to($patient->email)
                        ->subject('Your Medications Are Approved')
                        ->attach($file1, ['as' => 'Bactine.pdf'])
                        ->attach($file2, ['as' => 'Lidocaine.pdf']);
                });

                // Send email to artist if available
                if ($artist && $artist->email) {
                    Mail::send('emails/artist_approved', ['patient' => $patient, 'artist' => $artist], function ($message) use ($artist, $file1, $file2) {
                        $message->to($artist->email)
                            ->subject('Your Patient Has Been Approved')
                            ->attach($file1, ['as' => 'Bactine.pdf'])
                            ->attach($file2, ['as' => 'Lidocaine.pdf']);
                    });
                }

                // Delete temp files
                unlink($file1);
                unlink($file2);

                // Update status to approved
                $patient->patientsCQI->status = 1;
                $patient->patientsCQI->save();
            } catch (\Exception $e) {
                // Log and continue loop instead of stopping
                \Log::error('Bulk approval error for patient ' . $patient->id . ': ' . $e->getMessage());
                continue;
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

        // Send email to patient
        Mail::send('emails/patient_rejection', ['patient' => $patient], function ($message) use ($patient) {
            $message->to($patient->email)
                ->subject('Your Prescriptions Could Not Be Approved - DO NOT REPLY TO THIS EMAIL');
        });

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


    public function training()
    {

        return view('info/training');
    }
}
