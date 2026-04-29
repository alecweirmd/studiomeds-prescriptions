<?php

namespace App\Http\Controllers;

use App\Models\UtmVisit;
use Illuminate\Http\Request;

class UtmController extends Controller
{
    public function trackVisit(Request $request)
    {
        $sessionId = trim((string) $request->input('session_id', ''));
        if ($sessionId === '') {
            return response()->json(['ok' => false], 200);
        }

        $source   = $this->normalize($request->input('utm_source'));
        $medium   = $this->normalize($request->input('utm_medium'));
        $campaign = $this->normalize($request->input('utm_campaign'));

        $visit = UtmVisit::where('session_id', $sessionId)->first();

        if ($visit) {
            if ($source !== null)   { $visit->utm_source   = $source; }
            if ($medium !== null)   { $visit->utm_medium   = $medium; }
            if ($campaign !== null) { $visit->utm_campaign = $campaign; }
            $visit->last_touch_at = now();
            $visit->save();
        } else {
            UtmVisit::create([
                'session_id'     => $sessionId,
                'utm_source'     => $source,
                'utm_medium'     => $medium,
                'utm_campaign'   => $campaign,
                'first_touch_at' => now(),
                'last_touch_at'  => now(),
                'completed'      => false,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function normalize($val)
    {
        if ($val === null) {
            return null;
        }
        $val = trim((string) $val);
        if ($val === '') {
            return null;
        }
        return mb_substr($val, 0, 255);
    }
}
