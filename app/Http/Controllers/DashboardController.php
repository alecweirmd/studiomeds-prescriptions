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

            $allPatients = Patients::with('patientsCQI')
                ->get()
                ->whereNotNull('first_name');

            $data['patients'] = $allPatients
                ->sortBy(fn($p) => $p->patientsCQI ? $p->patientsCQI->status : 0)
                ->groupBy(fn($p) => $p->patientsCQI ? $p->patientsCQI->status : 0);

            // Build archive structure for Approved (1) and Rejected (2) tabs
            $archiveData = [];
            $currentMonthStart = now()->startOfMonth();
            $currentYear = now()->year;

            foreach ([1, 2] as $status) {
                $statusPatients = $allPatients
                    ->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == $status)
                    ->sortByDesc('created_at');

                $current = $statusPatients
                    ->filter(fn($p) => $p->created_at->gte($currentMonthStart))
                    ->values();

                $archived = $statusPatients
                    ->filter(fn($p) => $p->created_at->lt($currentMonthStart));

                $monthGroups = [];
                $yearGroups  = [];

                foreach ($archived as $p) {
                    $year     = $p->created_at->year;
                    $monthKey = $p->created_at->format('Y-m');

                    if ($year === $currentYear) {
                        if (!isset($monthGroups[$monthKey])) {
                            $monthGroups[$monthKey] = ['label' => $p->created_at->format('F Y'), 'patients' => collect()];
                        }
                        $monthGroups[$monthKey]['patients']->push($p);
                    } else {
                        if (!isset($yearGroups[$year])) {
                            $yearGroups[$year] = ['months' => []];
                        }
                        if (!isset($yearGroups[$year]['months'][$monthKey])) {
                            $yearGroups[$year]['months'][$monthKey] = ['label' => $p->created_at->format('F Y'), 'patients' => collect()];
                        }
                        $yearGroups[$year]['months'][$monthKey]['patients']->push($p);
                    }
                }

                krsort($monthGroups);
                krsort($yearGroups);
                foreach ($yearGroups as &$yg) {
                    krsort($yg['months']);
                }
                unset($yg);

                $archive = [];
                foreach ($monthGroups as $key => $mg) {
                    $archive[] = ['type' => 'month', 'key' => $key, 'label' => $mg['label'], 'patients' => $mg['patients']];
                }
                foreach ($yearGroups as $year => $yg) {
                    $months = [];
                    foreach ($yg['months'] as $mKey => $mg) {
                        $months[] = ['key' => $mKey, 'label' => $mg['label'], 'patients' => $mg['patients']];
                    }
                    $archive[] = [
                        'type'   => 'year',
                        'key'    => 'year_' . $year,
                        'label'  => (string) $year,
                        'months' => $months,
                        'count'  => array_sum(array_map(fn($m) => $m['patients']->count(), $months)),
                    ];
                }

                $archiveData[$status] = ['current' => $current, 'archive' => $archive];
            }

            $data['archiveData'] = $archiveData;

            // Acknowledgement lookup for Pending tab tooltip (keyed by patient_id)
            $data['patientAcknowledgements'] = PatientAcknowledgement::whereNotNull('patient_id')
                ->latest('acknowledged_at')
                ->get()
                ->keyBy('patient_id');

            // Flagged submissions with archiving
            $data['questionLabels'] = [
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

            $allFlagged = PatientAcknowledgement::with(['patient.patientsCQI'])
                ->whereNotNull('patient_id')
                ->whereNotNull('pdf_path')
                ->latest('acknowledged_at')
                ->get();

            $currentFlagged  = $allFlagged->filter(fn($a) => $a->acknowledged_at->gte($currentMonthStart))->values();
            $archivedFlagged = $allFlagged->filter(fn($a) => $a->acknowledged_at->lt($currentMonthStart));

            $fMonthGroups = [];
            $fYearGroups  = [];

            foreach ($archivedFlagged as $ack) {
                $year     = $ack->acknowledged_at->year;
                $monthKey = $ack->acknowledged_at->format('Y-m');

                if ($year === $currentYear) {
                    if (!isset($fMonthGroups[$monthKey])) {
                        $fMonthGroups[$monthKey] = ['label' => $ack->acknowledged_at->format('F Y'), 'items' => collect()];
                    }
                    $fMonthGroups[$monthKey]['items']->push($ack);
                } else {
                    if (!isset($fYearGroups[$year])) {
                        $fYearGroups[$year] = ['months' => []];
                    }
                    if (!isset($fYearGroups[$year]['months'][$monthKey])) {
                        $fYearGroups[$year]['months'][$monthKey] = ['label' => $ack->acknowledged_at->format('F Y'), 'items' => collect()];
                    }
                    $fYearGroups[$year]['months'][$monthKey]['items']->push($ack);
                }
            }

            krsort($fMonthGroups);
            krsort($fYearGroups);
            foreach ($fYearGroups as &$fyg) {
                krsort($fyg['months']);
            }
            unset($fyg);

            $flaggedArchive = [];
            foreach ($fMonthGroups as $key => $mg) {
                $flaggedArchive[] = ['type' => 'month', 'key' => $key, 'label' => $mg['label'], 'items' => $mg['items']];
            }
            foreach ($fYearGroups as $year => $yg) {
                $months = [];
                foreach ($yg['months'] as $mKey => $mg) {
                    $months[] = ['key' => $mKey, 'label' => $mg['label'], 'items' => $mg['items']];
                }
                $flaggedArchive[] = [
                    'type'   => 'year',
                    'key'    => 'year_' . $year,
                    'label'  => (string) $year,
                    'months' => $months,
                    'count'  => array_sum(array_map(fn($m) => $m['items']->count(), $months)),
                ];
            }

            $data['flaggedData'] = ['current' => $currentFlagged, 'archive' => $flaggedArchive];

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

        // ── This month stats ──────────────────────────────────────────
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $allThisMonth = Patients::with('patientsCQI')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotNull('first_name')
            ->get();

        $total         = $allThisMonth->count();
        $approved      = $allThisMonth->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 1)->count();
        $rejected      = $allThisMonth->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 2)->count();
        $revenue       = $approved * 35.00;
        $approvalRate  = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        // ── City breakdown (all time) ─────────────────────────────────
        $cityBreakdown = Patients::whereNotNull('city')
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->pluck('count', 'city');

        // ── Revenue trend — last 12 months ────────────────────────────
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
        $approvedLast12  = Patients::with('patientsCQI')
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->whereNotNull('first_name')
            ->get()
            ->filter(fn($p) => $p->patientsCQI && $p->patientsCQI->status == 1);

        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $cnt   = $approvedLast12->filter(
                fn($p) => $p->created_at->gte($start) && $p->created_at->lte($end)
            )->count();
            $revenueTrend[] = ['label' => $start->format('M Y'), 'revenue' => $cnt * 35.00];
        }

        // ── Submission time of day (all time) ─────────────────────────
        $hourCounts = array_fill(0, 24, 0);
        foreach (Patients::whereNotNull('first_name')->pluck('created_at') as $ts) {
            $hourCounts[$ts->hour]++;
        }

        // ── Returning patients (all time) ─────────────────────────────
        $byEmail = Patients::whereNotNull('email')
            ->whereNotNull('first_name')
            ->get()
            ->groupBy('email')
            ->filter(fn($g) => $g->count() > 1);

        $returningCount = $byEmail->count();
        $totalDays      = 0;
        $returningList  = [];

        foreach ($byEmail as $email => $subs) {
            $sorted = $subs->sortBy('created_at')->values();
            $days   = (int) $sorted[0]->created_at->diffInDays($sorted[1]->created_at);
            $totalDays += $days;
            $returningList[] = [
                'name'        => $sorted[0]->first_name . ' ' . $sorted[0]->last_name,
                'email'       => $email,
                'submissions' => $sorted->map(fn($s) => $s->created_at->format('m/d/Y'))->all(),
                'days'        => $days,
            ];
        }

        $avgDaysBetween = $returningCount > 0 ? round($totalDays / $returningCount, 1) : 0;

        return view('dashboards/analytics', compact(
            'total', 'approved', 'rejected', 'revenue',
            'approvalRate', 'rejectionRate', 'cityBreakdown',
            'revenueTrend', 'hourCounts',
            'returningCount', 'avgDaysBetween', 'returningList'
        ));
    }

    public function training()
    {

        return view('info/training');
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
