<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\OtpVerification;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Show public tracking portal entry form.
     */
    public function index(Request $request): View
    {
        return view('portal.track');
    }

    /**
     * Request OTP for tracking.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_number' => ['required', 'string', 'max:50'],
            'mobile' => ['required', 'string', 'max:20'],
        ]);

        $result = $this->otpService->requestOtp($validated['application_number'], $validated['mobile']);

        return response()->json($result);
    }

    /**
     * Verify OTP and return tracking view.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_number' => ['required', 'string', 'max:50'],
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $result = $this->otpService->verifyOtp(
            $validated['application_number'],
            $validated['mobile'],
            $validated['otp']
        );

        if ($result['success']) {
            $request->session()->put('tracking_token', $result['tracking_token']);
            $request->session()->put('tracking_sr', $validated['application_number']);
            $result['redirect_url'] = route('portal.status', ['token' => $result['tracking_token']]);
        }

        return response()->json($result);
    }

    /**
     * View tracking status of verified application.
     */
    public function status(Request $request, string $token): View|RedirectResponse
    {
        $verification = OtpVerification::where('tracking_token', $token)
            ->where('is_verified', true)
            ->where('created_at', '>=', now()->subHours(2)) // 2 hour valid session
            ->first();

        if (!$verification) {
            return redirect()->route('portal.track')->withErrors(['error' => 'Your tracking session has expired. Please verify again.']);
        }

        $cleanedMobile = preg_replace('/[^0-9]/', '', $verification->mobile);

        $application = Application::withoutGlobalScopes()
            ->where('application_number', $verification->application_number)
            ->whereHas('customer', function ($q) use ($cleanedMobile, $verification) {
                $q->withoutGlobalScopes()
                  ->where(function ($sub) use ($cleanedMobile, $verification) {
                      $sub->where('mobile', $verification->mobile)
                          ->orWhere('mobile', $cleanedMobile);
                  });
            })
            ->with([
                'customer',
                'service.category',
                'documents',
                'statusHistories' => function ($q) {
                    $q->select('id', 'application_id', 'new_status', 'remarks', 'created_at')->orderBy('created_at', 'asc');
                }
            ])
            ->firstOrFail();

        return view('portal.status', compact('application', 'verification'));
    }
}
