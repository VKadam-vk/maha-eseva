<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Clean phone number to E.164 format without '+' for WhatsApp (e.g. 919876543210).
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) === 10) {
            return '91' . $cleaned; // Add India country code by default
        }
        if (str_starts_with($cleaned, '0') && strlen($cleaned) === 11) {
            return '91' . substr($cleaned, 1);
        }
        return $cleaned;
    }

    /**
     * Check if WhatsApp is configured and enabled for tenant.
     */
    public static function isConfigured(?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? Tenant::first();
        if (!$tenant) {
            return false;
        }
        $settings = $tenant->settings_json ?? [];
        return !empty($settings['whatsapp_phone_number_id']) && !empty($settings['whatsapp_access_token']);
    }

    /**
     * Send message via Meta WhatsApp Cloud API.
     */
    public function sendMessage(string $recipientPhone, string $message, ?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? Tenant::first();
        if (!$tenant) {
            return ['success' => false, 'error' => 'Tenant not found'];
        }

        $settings = $tenant->settings_json ?? [];
        $phoneNumberId = $settings['whatsapp_phone_number_id'] ?? null;
        $accessToken = $settings['whatsapp_access_token'] ?? null;

        if (empty($phoneNumberId) || empty($accessToken)) {
            return ['success' => false, 'error' => 'WhatsApp Cloud API credentials not configured in Settings.'];
        }

        $formattedTo = self::formatPhoneNumber($recipientPhone);
        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages";

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedTo,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['messages'][0]['id'])) {
                $msgId = $data['messages'][0]['id'];
                Log::info("[WhatsApp Meta] Message sent to {$formattedTo}, ID: {$msgId}");

                return [
                    'success' => true,
                    'message_id' => $msgId,
                    'response' => $data,
                ];
            }

            $errorMessage = $data['error']['message'] ?? $response->body();
            Log::error("[WhatsApp Meta] Failed sending to {$formattedTo}: {$errorMessage}");

            return [
                'success' => false,
                'error' => $errorMessage,
                'details' => $data['error'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("[WhatsApp Meta] Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send official pre-approved Meta Template (e.g. 'hello_world' which works guaranteed on test numbers!).
     */
    public function sendTemplate(
        string $recipientPhone,
        string $templateName = 'application_status',
        string $langCode = 'mr',
        mixed $components = [],
        ?Tenant $tenant = null
    ): array {
        // Support backward-compatible 4-argument call (recipient, template, lang, tenant)
        if ($components instanceof Tenant) {
            $tenant = $components;
            $components = [];
        }

        $tenant = $tenant ?? Tenant::first();
        if (!$tenant) {
            return ['success' => false, 'error' => 'Tenant not found'];
        }

        $settings = $tenant->settings_json ?? [];
        $phoneNumberId = $settings['whatsapp_phone_number_id'] ?? null;
        $accessToken = $settings['whatsapp_access_token'] ?? null;

        if (empty($phoneNumberId) || empty($accessToken)) {
            return ['success' => false, 'error' => 'WhatsApp Cloud API credentials not configured in Settings.'];
        }

        $formattedTo = self::formatPhoneNumber($recipientPhone);
        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages";

        try {
            $templatePayload = [
                'name' => $templateName,
                'language' => [
                    'code' => $langCode,
                ],
            ];

            if (!empty($components) && is_array($components)) {
                $templatePayload['components'] = $components;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $formattedTo,
                'type' => 'template',
                'template' => $templatePayload,
            ];

            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['messages'][0]['id'])) {
                $msgId = $data['messages'][0]['id'];
                Log::info("[WhatsApp Meta Template] Sent {$templateName} ({$langCode}) to {$formattedTo}, ID: {$msgId}");

                return [
                    'success' => true,
                    'message_id' => $msgId,
                    'response' => $data,
                ];
            }

            $errorMessage = $data['error']['message'] ?? $response->body();
            Log::error("[WhatsApp Meta Template] Failed to {$formattedTo}: {$errorMessage}");

            return [
                'success' => false,
                'error' => $errorMessage,
                'details' => $data['error'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("[WhatsApp Meta Template] Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send Application Status Update via WhatsApp.
     * Uses the officially approved Meta Utility Template ('application_status' or 'application_update')
     * so it delivers guaranteed to any citizen even outside the Meta 24-hour window!
     */
    public function sendApplicationStatusNotification(Application $application, ?string $customStatus = null): array
    {
        $customer = $application->customer;
        if (!$customer || empty($customer->mobile)) {
            return ['success' => false, 'error' => 'Customer has no valid mobile number.'];
        }

        $tenant = $application->tenant ?? Tenant::first();
        $settings = $tenant?->settings_json ?? [];
        $preferredLang = $settings['whatsapp_default_template_lang'] ?? 'mr';

        $status = $customStatus ?? $application->work_status;
        $serviceName = $application->service?->sub_service_name ?: $application->service?->main_service_name ?: 'सेवा';
        $customerName = !empty($customer->name) ? $customer->name : 'नागरिक';
        $appNumber = !empty($application->application_number) ? $application->application_number : ('SR-' . $application->id);
        $centerName = $tenant?->name ?? 'Maha E-Seva Kendra';
        $centerPhone = $tenant?->contact_mobile ?? '';

        // 1. Dispatch via approved Meta Utility template
        // Meta template parameters: {{1}}=Name, {{2}}=Service, {{3}}=Status, {{4}}=Application No
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => mb_substr($customerName, 0, 60)],
                    ['type' => 'text', 'text' => mb_substr($serviceName, 0, 60)],
                    ['type' => 'text', 'text' => mb_substr($status, 0, 40)],
                    ['type' => 'text', 'text' => mb_substr($appNumber, 0, 40)],
                ],
            ],
        ];

        if ($preferredLang === 'en') {
            $res = $this->sendTemplate($customer->mobile, 'application_update', 'en', $components, $tenant);
            if (!$res['success'] && str_contains(strtolower($res['error'] ?? ''), 'language')) {
                $res = $this->sendTemplate($customer->mobile, 'application_update', 'en_US', $components, $tenant);
            }
        } else {
            $res = $this->sendTemplate($customer->mobile, 'application_status', 'mr', $components, $tenant);
        }

        // Fallback: If template dispatch fails (e.g. template issue), fall back to custom text message
        if (!$res['success']) {
            Log::warning("[WhatsApp Status Notification] Template dispatch failed: " . ($res['error'] ?? '') . ". Retrying via custom message.");
            
            $remarksText = '';
            if ($status === 'REJECTED' && !empty($application->pending_remarks)) {
                $remarksText = "\n⚠️ त्रुटी रिमार्क: {$application->pending_remarks}\n";
            } elseif (in_array($status, ['APPROVED', 'READY', 'COMPLETED'])) {
                $remarksText = "\n🎉 *तुमचा दाखला/कागदपत्र तयार आहे.* कृपया केंद्रात येऊन मूळ प्रत घेऊन जावी.\n";
            }

            $customFormat = trim($settings['whatsapp_status_msg_format'] ?? '');
            if (!empty($customFormat)) {
                $msg = str_replace(
                    ['{customer_name}', '{service_name}', '{status}', '{application_no}', '{center_name}', '{center_mobile}', '{remarks}', '{date}'],
                    [$customerName, $serviceName, $status, $appNumber, $centerName, $centerPhone, $remarksText, now()->format('d/m/Y h:i A')],
                    $customFormat
                );
            } else {
                $statusEmoji = match ($status) {
                    'APPROVED', 'READY', 'COMPLETED' => '✅',
                    'REJECTED', 'CANCELLED' => '❌',
                    'IN_PROGRESS' => '⏳',
                    default => '📋',
                };

                $msg = "{$statusEmoji} *{$centerName} - अर्ज स्थिती अपडेट*\n\n"
                     . "नमस्कार *{$customerName}*,\n"
                     . "तुमच्या *{$serviceName}* अर्जाची स्थिती (Status) आता *{$status}* झाली आहे.\n"
                     . "• अर्ज क्र. (SR): *{$appNumber}*\n"
                     . $remarksText
                     . "\nधन्यवाद!\n_{$centerName}_";
            }

            $res = $this->sendMessage($customer->mobile, $msg, $tenant);
        }

        // Record in notification logs
        NotificationLog::create([
            'tenant_id' => $application->tenant_id,
            'customer_id' => $customer->id,
            'type' => 'STATUS_UPDATE',
            'channel' => 'WHATSAPP',
            'recipient' => $customer->mobile,
            'title' => "WhatsApp: Status {$status}",
            'message' => "Template: " . ($preferredLang === 'en' ? 'application_update' : 'application_status') . " | Status: {$status}",
            'status' => $res['success'] ? 'SENT' : 'FAILED',
            'provider_response' => json_encode($res),
            'sent_at' => now(),
        ]);

        return $res;
    }
}
