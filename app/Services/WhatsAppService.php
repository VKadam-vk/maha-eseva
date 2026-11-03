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
    public function sendTemplate(string $recipientPhone, string $templateName = 'hello_world', string $langCode = 'en_US', ?Tenant $tenant = null): array
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
                'to' => $formattedTo,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $langCode,
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['messages'][0]['id'])) {
                $msgId = $data['messages'][0]['id'];
                Log::info("[WhatsApp Meta Template] Sent {$templateName} to {$formattedTo}, ID: {$msgId}");

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
     */
    public function sendApplicationStatusNotification(Application $application, ?string $customStatus = null): array
    {
        $customer = $application->customer;
        if (!$customer || empty($customer->mobile)) {
            return ['success' => false, 'error' => 'Customer has no valid mobile number.'];
        }

        $status = $customStatus ?? $application->work_status;
        $serviceName = $application->service?->sub_service_name ?: $application->service?->main_service_name ?: 'Service';
        $centerName = $application->tenant?->name ?? 'Maha E-Seva Kendra';

        $statusEmoji = match ($status) {
            'APPROVED', 'READY' => '✅',
            'REJECTED', 'CANCELLED' => '❌',
            'IN_PROGRESS' => '⏳',
            default => '📋',
        };

        $msg = "{$statusEmoji} *{$centerName} - अर्ज स्थिती अपडेट*\n\n"
             . "नमस्कार *{$customer->name}*,\n"
             . "तुमच्या *{$serviceName}* अर्जाची स्थिती (Status) आता *{$status}* झाली आहे.\n"
             . "• अर्ज क्र. (SR): *{$application->application_number}*\n";

        if (in_array($status, ['APPROVED', 'READY'])) {
            $msg .= "\n🎉 *तुमचा दाखला/कागदपत्र तयार आहे.* कृपया केंद्रात येऊन मूळ प्रत घेऊन जावी.\n";
        } elseif ($status === 'REJECTED' && !empty($application->pending_remarks)) {
            $msg .= "\n⚠️ त्रुटी रिमार्क: {$application->pending_remarks}\n";
        }

        $msg .= "\nधन्यवाद!\n_{$centerName}_";

        $res = $this->sendMessage($customer->mobile, $msg, $application->tenant);

        // Record in notification logs
        NotificationLog::create([
            'tenant_id' => $application->tenant_id,
            'customer_id' => $customer->id,
            'type' => 'STATUS_UPDATE',
            'channel' => 'WHATSAPP',
            'recipient' => $customer->mobile,
            'title' => "WhatsApp: Status {$status}",
            'message' => $msg,
            'status' => $res['success'] ? 'SENT' : 'FAILED',
            'provider_response' => json_encode($res),
            'sent_at' => now(),
        ]);

        return $res;
    }
}
