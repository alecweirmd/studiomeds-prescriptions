<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\FormStart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedIntakeReport extends Command
{
    protected $signature = 'intakes:abandoned-report';

    protected $description = 'Email a daily consolidated admin report: unresolved alerts (past 24h) plus abandoned intakes';

    public function handle()
    {
        // Unresolved alerts created in the last 24 hours. Alerts resolved before
        // this run are excluded by the resolved_at null check (Fix 3).
        $alerts = Alert::whereNull('resolved_at')
            ->where('created_at', '>=', now()->subDay())
            ->with('alertable')
            ->latest()
            ->get();

        $abandonedCount = FormStart::whereNotNull('abandoned_at')
            ->whereNull('contacted_at')
            ->whereNull('dismissed_at')
            ->count();

        $alertsUrl = 'https://prescriptions.studiomeds.com/dashboard/alerts';
        $loginUrl  = 'https://prescriptions.studiomeds.com/login';

        $allQuiet = $alerts->isEmpty() && $abandonedCount === 0;

        // --- Section 1: Alerts (past 24 hours) ---
        $alertsSection = "ALERTS (PAST 24 HOURS)\n";
        $alertsSection .= "----------------------\n";
        if ($alerts->isEmpty()) {
            $alertsSection .= "No unresolved alerts in the past 24 hours.\n";
        } else {
            foreach ($alerts as $alert) {
                $type = ucwords(str_replace('_', ' ', $alert->type));
                $alertsSection .= "- [{$type}] {$this->patientIdentifier($alert)} — created {$alert->created_at->format('M j, Y g:i A')}\n";
            }
            $alertsSection .= "\nReview and act on these here: {$alertsUrl}\n";
        }

        // --- Section 2: Abandoned intakes (existing logic) ---
        $intakesSection = "ABANDONED INTAKES\n";
        $intakesSection .= "-----------------\n";
        if ($abandonedCount === 0) {
            $intakesSection .= "No abandoned intakes.\n";
        } else {
            $intakesSection .= "You currently have {$abandonedCount} abandoned "
                . \Illuminate\Support\Str::plural('intake', $abandonedCount)
                . " awaiting follow-up.\n";
            $intakesSection .= "Log in to review them here: {$loginUrl}\n";
        }

        // --- Assemble body ---
        if ($allQuiet) {
            $body = "All quiet — no unresolved alerts and no abandoned intakes in the past 24 hours.\n"
                . "This is your daily confirmation that the report ran successfully.\n\n"
                . $alertsSection . "\n" . $intakesSection;
        } else {
            $body = "Your daily StudioMeds admin report.\n\n"
                . $alertsSection . "\n" . $intakesSection;
        }

        // Subject mirrors the existing pattern, extended to cover both sections.
        if ($allQuiet) {
            $subject = 'StudioMeds Daily Report - All quiet';
        } else {
            $alertCount = $alerts->count();
            $subject = "StudioMeds Daily Report - {$alertCount} "
                . \Illuminate\Support\Str::plural('alert', $alertCount)
                . ", {$abandonedCount} abandoned "
                . \Illuminate\Support\Str::plural('intake', $abandonedCount);
        }

        // Fire every day regardless of content (cron health check). No early
        // return — quiet days still send the confirmation above.
        Mail::raw($body, function ($m) use ($subject) {
            $m->to('admin@studiomeds.com')
              ->from(config('services.admin.from_email'))
              ->subject($subject);
        });
    }

    /**
     * Build a human-readable patient identifier from an alert's polymorphic
     * payload. Both current alert payloads (Patients for email_delivery_failed,
     * PendingSubmission for charge_no_record) expose first_name/last_name/email.
     */
    private function patientIdentifier(Alert $alert): string
    {
        $sub = $alert->alertable;
        if (!$sub) {
            return 'Unknown patient';
        }

        $name = trim(($sub->first_name ?? '') . ' ' . ($sub->last_name ?? ''));
        $email = $sub->email ?? null;

        if ($name !== '' && $email) {
            return "{$name} ({$email})";
        }
        if ($name !== '') {
            return $name;
        }
        if ($email) {
            return $email;
        }
        return 'Unknown patient';
    }
}
