<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$session = \Illuminate\Support\Facades\DB::table('sessions')->orderByDesc('last_activity')->first();
if ($session) {
    echo "Latest session user_id: " . $session->user_id . "\n";
    $payload = @unserialize(base64_decode($session->payload));
    echo "Payload keys: " . json_encode(array_keys($payload ?? [])) . "\n";
    $user = \App\Models\User::find($session->user_id);
    echo "User: " . ($user ? $user->email . " (Tenant: " . $user->tenant_id . ")" : 'GUEST') . "\n";
}
