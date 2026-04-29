<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;

class ReferralCodeController extends Controller
{
    const BASE_PRICE = 35.00;

    public function validateCode(Request $request)
    {
        $codeInput = trim((string) $request->input('code', ''));

        if ($codeInput === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a referral code.',
            ]);
        }

        $code = DiscountCode::whereRaw('LOWER(code_string) = ?', [strtolower($codeInput)])->first();

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'That code is not valid.',
            ]);
        }

        if ($code->status !== 'active') {
            $message = 'That code is no longer available.';
            if ($code->status === 'expired')   $message = 'That code has expired.';
            if ($code->status === 'exhausted') $message = 'That code has reached its usage limit.';
            if ($code->status === 'paused')    $message = 'That code is currently paused.';
            return response()->json(['success' => false, 'message' => $message]);
        }

        if ($code->usage_count >= $code->usage_cap) {
            return response()->json([
                'success' => false,
                'message' => 'That code has reached its usage limit.',
            ]);
        }

        if ($code->expiration_date && $code->expiration_date->isBefore(now()->startOfDay())) {
            return response()->json([
                'success' => false,
                'message' => 'That code has expired.',
            ]);
        }

        $base = self::BASE_PRICE;
        $newAmount = $base;
        $discountAmount = 0;
        $successMessage = '';

        if ($code->discount_type === 'free') {
            $newAmount = 0.00;
            $discountAmount = $base;
            $successMessage = 'Code applied — your evaluation is fully comped!';
        } elseif ($code->discount_type === 'fixed_dollar_off') {
            $value = (float) $code->discount_value;
            $discountAmount = min($value, $base);
            $newAmount = max(0, round($base - $discountAmount, 2));
            $successMessage = 'Code applied — $' . number_format($discountAmount, 2) . ' off.';
        } elseif ($code->discount_type === 'percent_off') {
            $value = (float) $code->discount_value;
            $discountAmount = round($base * ($value / 100), 2);
            $newAmount = max(0, round($base - $discountAmount, 2));
            $successMessage = 'Code applied — ' . rtrim(rtrim(number_format($value, 2), '0'), '.') . '% off.';
        }

        return response()->json([
            'success'         => true,
            'code'            => $code->code_string,
            'discount_type'   => $code->discount_type,
            'discount_value'  => $code->discount_value !== null ? (float) $code->discount_value : null,
            'discount_amount' => round($discountAmount, 2),
            'new_amount'      => round($newAmount, 2),
            'base_amount'     => $base,
            'message'         => $successMessage,
        ]);
    }
}
