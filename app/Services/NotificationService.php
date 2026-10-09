<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send OTP SMS.
     */
    public function sendOtp(int $tenantId, string $mobile, string $otp, string $applicationNumber): void
    {
        $displayMessage = "Your Maha E-Seva OTP for application {$applicationNumber} is {$otp}. Valid for 10 minutes. Do not share with anyone.";
        $loggedMessage = "Your Maha E-Seva OTP for application {$applicationNumber} is [REDACTED_OTP]. Valid for 10 minutes. Do not share with anyone.";
        
        $this->dispatchNotification(
            $tenantId,
            'SMS',
            $mobile,
            'OTP Verification',
            $loggedMessage,
            'OTP'
        );
    }

    /**
     * Notify application created.
     */
    public function notifyApplicationCreated(Application $application): void
    {
        $customer = $application->customer;
        $service = $application->service;
        $msg = "Dear {$customer->name}, your application for {$service->main_service_name} ({$service->sub_service_name}) has been registered with SR Number: {$application->application_number}. Track online at " . url('/track');

        $this->dispatchNotification(
            $application->tenant_id,
            'SMS',
            $customer->mobile,
            'Application Registered',
            $msg,
            'APPLICATION_CREATED',
            null,
            $customer->id
        );
    }

    /**
     * Notify status updated.
     */
    public function notifyStatusChanged(Application $application, string $newStatus): void
    {
        $customer = $application->customer;
        $service = $application->service;
        $msg = "Dear {$customer->name}, your application {$application->application_number} for {$service->main_service_name} status updated to: {$newStatus}.";

        if ($newStatus === Application::STATUS_APPROVED || $newStatus === Application::STATUS_READY) {
            $msg .= " Your certificate/document is ready for pickup.";
        }

        $this->dispatchNotification(
            $application->tenant_id,
            'SMS',
            $customer->mobile,
            'Status Update: ' . $newStatus,
            $msg,
            'STATUS_CHANGED',
            null,
            $customer->id
        );
    }

    /**
     * Notify payment received.
     */
    public function notifyPaymentReceived(int $tenantId, Customer $customer, float $amount, string $receiptNo): void
    {
        $msg = "Dear {$customer->name}, received payment of Rs. " . number_format($amount, 2) . " against Receipt #{$receiptNo}. Thank you for using Maha E-Seva.";

        $this->dispatchNotification(
            $tenantId,
            'SMS',
            $customer->mobile,
            'Payment Received',
            $msg,
            'PAYMENT_RECEIVED',
            null,
            $customer->id
        );
    }

    /**
     * Internal dispatch abstraction.
     */
    protected function dispatchNotification(
        int $tenantId,
        string $channel,
        string $recipient,
        string $title,
        string $message,
        string $type,
        ?int $userId = null,
        ?int $customerId = null
    ): NotificationLog {
        // In local/production, provider driver sends HTTP request to SMS/WhatsApp gateway
        $providerResponse = 'OK: Simulated SMS Provider Gateway Dispatch';
        $status = 'SENT';

        try {
            // Future provider dispatch integration point
            Log::info("[Notification] [{$channel}] To: {$recipient} | {$title}: {$message}");
        } catch (\Exception $e) {
            $status = 'FAILED';
            $providerResponse = $e->getMessage();
        }

        return NotificationLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'customer_id' => $customerId,
            'type' => $type,
            'channel' => $channel,
            'recipient' => $recipient,
            'title' => $title,
            'message' => $message,
            'status' => $status,
            'provider_response' => $providerResponse,
            'sent_at' => now(),
        ]);
    }
}
