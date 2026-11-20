<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::first();
$settings = $tenant?->settings_json ?? [];
$phoneId = $settings['whatsapp_phone_number_id'] ?? '';
$token = $settings['whatsapp_access_token'] ?? '';

echo "Tenant Name: " . $tenant?->name . "\n";
echo "Phone ID: " . $phoneId . "\n";
echo "Token: " . (strlen($token) > 20 ? substr($token, 0, 15) . '...' . substr($token, -10) : 'EMPTY') . "\n";

if ($phoneId && $token) {
    // 1. Phone number details
    $phoneResp = \Illuminate\Support\Facades\Http::withToken($token)
        ->get("https://graph.facebook.com/v21.0/{$phoneId}?fields=id,display_phone_number,verified_name,quality_rating,whatsapp_business_account,code_verification_status,status");
    echo "\n--- Phone Details ---\n";
    print_r($phoneResp->json());

    $wabaId = $phoneResp->json('whatsapp_business_account.id');
    echo "\n--- WABA ID: {$wabaId} ---\n";

    if ($wabaId) {
        // 2. WABA details including currency & payment status
        $wabaResp = \Illuminate\Support\Facades\Http::withToken($token)
            ->get("https://graph.facebook.com/v21.0/{$wabaId}?fields=id,name,currency,timezone_id,account_review_status,message_template_namespace");
        echo "\n--- WABA Details ---\n";
        print_r($wabaResp->json());

        // 3. Template details
        $tmplResp = \Illuminate\Support\Facades\Http::withToken($token)
            ->get("https://graph.facebook.com/v21.0/{$wabaId}/message_templates");
        echo "\n--- Templates in WABA ---\n";
        print_r($tmplResp->json());
    }
}
