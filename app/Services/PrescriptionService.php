<?php

namespace App\Services;

use App\Models\Patients;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Centralizes the prescription document set and its on-disk persistence.
 *
 * The document set is procedure-dependent and mirrors what the approval email
 * attaches: lip_blush / eyeliner receive a single Zensa prescription, while
 * tattoo / brow_pmu / legacy patients receive Bactine + Lidocaine.
 *
 * Persistence is additive — PDFs are a deterministic function of the patient
 * record, so stored copies (captured at approval time) are served when present
 * and regenerated on demand as a fallback for approvals predating this layer.
 */
class PrescriptionService
{
    /**
     * The prescription documents for a patient.
     * Each: key (stable id used in routes/paths), name (download/attachment filename base,
     * kept identical to the emailed attachment), label (UI display), view (Blade).
     *
     * @return array<int, array{key:string, name:string, label:string, view:string}>
     */
    public function documentsFor(Patients $patient): array
    {
        $procedure = $patient->procedure_type;

        if ($procedure === 'lip_blush' || $procedure === 'eyeliner') {
            return [
                ['key' => 'zensa', 'name' => 'Zensa Prescription', 'label' => 'Zensa Prescription', 'view' => 'pdf/zensa'],
            ];
        }

        // Default branch: tattoo, brow_pmu, or unset procedure_type (legacy patients)
        return [
            ['key' => 'bactine',   'name' => 'Bactine',         'label' => 'Bactine Prescription',   'view' => 'pdf/bactine'],
            ['key' => 'lidocaine', 'name' => 'Lidocaine Cream', 'label' => 'Lidocaine Prescription', 'view' => 'pdf/lidocaine'],
        ];
    }

    /**
     * Resolve a single document by key, defaulting to the first when none given.
     *
     * @return array{key:string, name:string, label:string, view:string}
     */
    public function documentByKey(Patients $patient, ?string $key): array
    {
        $docs = $this->documentsFor($patient);

        if ($key === null || $key === '') {
            return $docs[0];
        }

        foreach ($docs as $doc) {
            if ($doc['key'] === $key) {
                return $doc;
            }
        }

        abort(404, 'Unknown prescription document.');
    }

    /**
     * Storage path (relative to the default disk) for a document key.
     */
    public function storagePath(Patients $patient, string $key): string
    {
        return "prescriptions/{$patient->id}/{$key}.pdf";
    }

    /**
     * Generate and persist all prescription PDFs for a patient.
     * Returns the issuance record as [{key, name, path}] for storing on the CQI row.
     *
     * @return array<int, array{key:string, name:string, path:string}>
     */
    public function storeFor(Patients $patient): array
    {
        $stored = [];

        foreach ($this->documentsFor($patient) as $doc) {
            $path = $this->storagePath($patient, $doc['key']);
            Storage::put($path, Pdf::loadView($doc['view'], ['patient' => $patient])->output());

            $stored[] = ['key' => $doc['key'], 'name' => $doc['name'], 'path' => $path];
        }

        return $stored;
    }

    /**
     * Raw PDF bytes for a single document — served from disk when stored,
     * regenerated on demand otherwise (legacy approvals).
     */
    public function pdfBytes(Patients $patient, string $key): string
    {
        $path = $this->storagePath($patient, $key);

        if (Storage::exists($path)) {
            return Storage::get($path);
        }

        $doc = $this->documentByKey($patient, $key);

        return Pdf::loadView($doc['view'], ['patient' => $patient])->output();
    }
}
