<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show settings and organization preferences.
     */
    public function index(): View
    {
        $tenant = Auth::user()->tenant;
        $employees = $tenant ? $tenant->employees()->where('status', 'ACTIVE')->with('user')->get() : collect();
        return view('settings.index', compact('tenant', 'employees'));
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
            'advertising_banner_enabled' => ['nullable'],
            'advertising_banner_tag' => ['nullable', 'string', 'max:50'],
            'advertising_banner_text' => ['nullable', 'string', 'max:3000'],
            'advertising_banner_visibility' => ['nullable', 'string', 'in:ALL,SPECIFIC'],
            'advertising_banner_employee_ids' => ['nullable', 'array'],
            'advertising_banner_employee_ids.*' => ['integer'],
        ]);

        $tenant = $user->tenant;
        $oldSettings = $tenant->settings_json ?? [];
        $newSettings = array_merge($oldSettings, [
            'sms_sender_id' => $validated['sms_sender_id'] ?? 'MHSEVA',
            'whatsapp_enabled' => (bool) $request->has('whatsapp_enabled'),
            'whatsapp_phone_number_id' => trim($validated['whatsapp_phone_number_id'] ?? ''),
            'whatsapp_access_token' => trim($validated['whatsapp_access_token'] ?? ''),
            'whatsapp_auto_status_notify' => (bool) $request->has('whatsapp_auto_status_notify'),
            'advertising_banner_enabled' => (bool) $request->has('advertising_banner_enabled'),
            'advertising_banner_tag' => trim($request->input('advertising_banner_tag', 'ADVERTISING')) ?: 'ADVERTISING',
            'advertising_banner_text' => trim($request->input('advertising_banner_text', '')) ?: '📢 महा ई-सेवा केंद्र विशेष सूचना / जाहिरात: नवीन पॅन कार्ड (२ तासांत), आधार अपडेट, उत्पन्न दाखला, डोमिसाईल, नॉन-क्रिमिलेअर, लाडकी बहीण योजना, शेतकरी सन्मान निधी, रेशन कार्ड ई-केवायसी व सर्व शासकीय योजनांचे अर्ज आपल्या केंद्रावर जलद गतीने भरून मिळतील! त्वरित संपर्क साधा.',
            'advertising_banner_visibility' => $request->input('advertising_banner_visibility') === 'SPECIFIC' ? 'SPECIFIC' : 'ALL',
            'advertising_banner_employee_ids' => is_array($request->input('advertising_banner_employee_ids')) ? array_map('intval', $request->input('advertising_banner_employee_ids')) : [],
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
        if (!$user || !$user->isSoftwareOwner()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized: Only Software Owner can test WhatsApp Cloud API.'], 403);
        }

        $validated = $request->validate([
            'recipient' => ['required', 'string'],
            'type' => ['nullable', 'in:template,text,hello_world,app_status'],
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

        $type = $validated['type'] ?? 'app_status';

        if ($type === 'text') {
            $centerName = $tenant->name ?? 'Maha E-Seva Kendra';
            $msg = "🚀 *{$centerName} - WhatsApp Test Connection*\n\n"
                 . "हे महा ई-सेवा ERP सिस्टीममधील Meta Cloud API चे थेट चाचणी मेसेज आहे.\n"
                 . "वेळ: " . now()->format('d M Y, h:i A') . "\n"
                 . "स्टेटस: चालू (Active)";
            $res = $whatsAppService->sendMessage($validated['recipient'], $msg, $tenant);
        } elseif ($type === 'hello_world') {
            $res = $whatsAppService->sendTemplate($validated['recipient'], 'hello_world', 'en_US', [], $tenant);
        } else {
            $phoneId = trim($validated['phone_number_id'] ?: ($tenant->settings_json['whatsapp_phone_number_id'] ?? ''));
            $token = trim($validated['access_token'] ?: ($tenant->settings_json['whatsapp_access_token'] ?? ''));

            $templateObj = null;
            $wabaId = null;
            $langCode = 'mr';

            // Query WABA ID and live template definition directly from Meta Graph API
            if ($phoneId && $token) {
                try {
                    $phoneResp = Http::withToken($token)->timeout(8)->get("https://graph.facebook.com/v21.0/{$phoneId}?fields=whatsapp_business_account");
                    $wabaId = $phoneResp->json('whatsapp_business_account.id');

                    if ($wabaId) {
                        $tmplResp = Http::withToken($token)->timeout(8)->get("https://graph.facebook.com/v21.0/{$wabaId}/message_templates?name=application_status");
                        $templates = $tmplResp->json('data') ?? [];
                        // Find active/approved Marathi template
                        foreach ($templates as $t) {
                            if (($t['name'] ?? '') === 'application_status') {
                                $templateObj = $t;
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('[Meta Template Query Exception]: ' . $e->getMessage());
                }
            }

            if (!empty($templateObj['language'])) {
                $langCode = $templateObj['language'];
            }

            // Inspect template placeholders count from Meta body
            $paramCount = 4;
            $bodyText = '';
            if (!empty($templateObj['components'])) {
                foreach ($templateObj['components'] as $c) {
                    if (($c['type'] ?? '') === 'BODY' && !empty($c['text'])) {
                        $bodyText = $c['text'];
                        preg_match_all('/\{\{(\d+)\}\}/', $c['text'], $m);
                        if (!empty($m[0])) {
                            $paramCount = count($m[0]);
                        }
                    }
                }
            }

            // Construct exact matching parameters
            $sampleValues = ['राहुल (चाचणी)', 'पॅन कार्ड सेवा', 'COMPLETED', 'SR-2026-TEST', 'महा ई-सेवा'];
            $bodyParams = [];
            for ($i = 0; $i < $paramCount; $i++) {
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => $sampleValues[$i] ?? 'माहिती',
                ];
            }

            $components = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ],
            ];

            $res = $whatsAppService->sendTemplate($validated['recipient'], 'application_status', $langCode, $components, $tenant);
            $res['waba_id'] = $wabaId;
            $res['meta_template'] = $templateObj;
            $res['used_language_code'] = $langCode;
            $res['param_count'] = $paramCount;
            $res['body_text'] = $bodyText;
        }

        return response()->json($res);
    }
}
