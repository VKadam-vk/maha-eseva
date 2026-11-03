<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show settings and organization preferences.
     */
    public function index(): View
    {
        $tenant = Auth::user()->tenant;
        return view('settings.index', compact('tenant'));
    }

    /**
     * Update tenant branding & contact settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
            abort(403, 'Unauthorized: Only business owner can modify settings.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_mobile' => ['required', 'string', 'max:20'],
            'contact_email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'sms_sender_id' => ['nullable', 'string'],
            'whatsapp_enabled' => ['nullable'],
            'whatsapp_phone_number_id' => ['nullable', 'string'],
            'whatsapp_access_token' => ['nullable', 'string'],
            'whatsapp_auto_status_notify' => ['nullable'],
        ]);

        $tenant = $user->tenant;
        $oldSettings = $tenant->settings_json ?? [];
        $newSettings = array_merge($oldSettings, [
            'sms_sender_id' => $validated['sms_sender_id'] ?? 'MHSEVA',
            'whatsapp_enabled' => (bool) $request->has('whatsapp_enabled'),
            'whatsapp_phone_number_id' => trim($validated['whatsapp_phone_number_id'] ?? ''),
            'whatsapp_access_token' => trim($validated['whatsapp_access_token'] ?? ''),
            'whatsapp_auto_status_notify' => (bool) $request->has('whatsapp_auto_status_notify'),
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'contact_name' => $validated['contact_name'],
            'contact_mobile' => $validated['contact_mobile'],
            'contact_email' => $validated['contact_email'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'settings_json' => $newSettings,
        ]);

        AuditService::log('SETTINGS_UPDATED', $tenant);

        return back()->with('success', 'Organization and WhatsApp settings updated successfully.');
    }

    /**
     * Test WhatsApp Cloud API connection in real-time.
     */
    public function testWhatsApp(Request $request, WhatsAppService $whatsAppService): JsonResponse
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isBusinessOwner()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'recipient' => ['required', 'string'],
            'type' => ['nullable', 'in:template,text'],
            'phone_number_id' => ['nullable', 'string'],
            'access_token' => ['nullable', 'string'],
        ]);

        $tenant = $user->tenant;

        // If credentials are submitted on the fly from the test form, apply them
        if (!empty($validated['phone_number_id']) && !empty($validated['access_token'])) {
            $settings = $tenant->settings_json ?? [];
            $settings['whatsapp_phone_number_id'] = trim($validated['phone_number_id']);
            $settings['whatsapp_access_token'] = trim($validated['access_token']);
            $tenant->settings_json = $settings;
        }

        $type = $validated['type'] ?? 'template';

        if ($type === 'text') {
            $centerName = $tenant->name ?? 'Maha E-Seva Kendra';
            $msg = "🚀 *{$centerName} - WhatsApp Test Connection*\n\n"
                 . "हे महा ई-सेवा ERP सिस्टीममधील Meta Cloud API चे थेट चाचणी मेसेज आहे.\n"
                 . "वेळ: " . now()->format('d M Y, h:i A') . "\n"
                 . "स्टेटस: चालू (Active)";
            $res = $whatsAppService->sendMessage($validated['recipient'], $msg, $tenant);
        } else {
            // Default pre-approved Meta template guaranteed to work with test numbers
            $res = $whatsAppService->sendTemplate($validated['recipient'], 'hello_world', 'en_US', $tenant);
        }

        return response()->json($res);
    }
}
