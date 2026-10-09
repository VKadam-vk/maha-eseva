<?php

namespace App\Services;

use App\Models\Application;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Request OTP for tracking an application.
     */
    public function requestOtp(string $applicationNumber, string $mobile): array
    {
        $cleanedMobile = preg_replace('/[^0-9]/', '', $mobile);

        // Find application matching SR and Mobile across all tenants
        $application = Application::withoutGlobalScopes()
            ->where('application_number', trim($applicationNumber))
            ->whereHas('customer', function ($q) use ($cleanedMobile, $mobile) {
                $q->withoutGlobalScopes()
                  ->where(function ($sub) use ($cleanedMobile, $mobile) {
                      $sub->where('mobile', $mobile)
                          ->orWhere('mobile', $cleanedMobile);
                  });
            })
            ->first();

        if (!$application) {
            return [
                'success' => false,
                'message' => 'No matching application found for the provided Application Number and Mobile.',
            ];
        }

        // Check recent OTP request for rate limiting cooldown (1 minute cooldown)
        $recent = OtpVerification::where('mobile', $mobile)
            ->where('application_number', $applicationNumber)
            ->where('created_at', '>=', now()->subSeconds(60))
            ->first();

        if ($recent) {
            return [
                'success' => false,
                'message' => 'Please wait at least 60 seconds before requesting a new OTP.',
            ];
        }

        // Generate 6-digit OTP
        $otp = sprintf('%06d', random_int(100000, 999999));
        $otpHash = hash('sha256', $otp);

        $verification = OtpVerification::create([
            'tenant_id' => $application->tenant_id,
            'mobile' => $mobile,
            'application_number' => $applicationNumber,
            'otp_hash' => $otpHash,
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'is_verified' => false,
            'ip_address' => Request::ip(),
        ]);

        // Send OTP notification
        app(NotificationService::class)->sendOtp($application->tenant_id, $mobile, $otp, $applicationNumber);

        return [
            'success' => true,
            'message' => 'OTP has been dispatched to your registered mobile number.',
            'debug_otp' => (config('app.env') !== 'production' && config('app.debug')) ? $otp : null,
        ];
    }

    /**
     * Verify OTP and return tracking session token.
     */
    public function verifyOtp(string $applicationNumber, string $mobile, string $enteredOtp): array
    {
        $verification = OtpVerification::where('mobile', $mobile)
            ->where('application_number', $applicationNumber)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP session. Please request a new OTP.',
            ];
        }

        if ($verification->attempts >= 5) {
            return [
                'success' => false,
                'message' => 'Maximum OTP verification attempts exceeded. Please request a new OTP.',
            ];
        }

        $inputHash = hash('sha256', trim($enteredOtp));

        if (!hash_equals($verification->otp_hash, $inputHash)) {
            $verification->increment('attempts');
            return [
                'success' => false,
                'message' => 'Invalid OTP code. Please check and try again. (' . (5 - $verification->attempts) . ' attempts remaining)',
            ];
        }

        // Successful verification -> generate secure tracking token
        $trackingToken = Str::random(64);
        $verification->update([
            'is_verified' => true,
            'tracking_token' => $trackingToken,
        ]);

        return [
            'success' => true,
            'tracking_token' => $trackingToken,
            'message' => 'OTP verified successfully.',
        ];
    }
}
