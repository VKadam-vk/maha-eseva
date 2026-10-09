<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'admin@mahaeseva.com')->first();
        $this->customer = Customer::first();
        $this->application = Application::first();
    }

    public function test_recording_payment_updates_application_balance_and_creates_receipt(): void
    {
        $initialReceived = $this->application->received_amount;
        $paymentAmount = 100.00;

        $response = $this->actingAs($this->user)->post('/billing/payments/record', [
            'customer_id' => $this->customer->id,
            'application_id' => $this->application->id,
            'amount' => $paymentAmount,
            'payment_mode' => 'UPI',
            'payment_date' => now()->toDateString(),
            'transaction_reference' => 'UPI9988776655',
            'notes' => 'Advance token payment',
        ]);

        $response->assertRedirect();

        $this->application->refresh();
        $this->assertEquals($initialReceived + $paymentAmount, $this->application->received_amount);

        // Verify Payment record created with unique receipt number
        $payment = Payment::where('transaction_reference', 'UPI9988776655')->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('REC-', $payment->receipt_number);
        $this->assertEquals('SUCCESS', $payment->payment_status);
    }

    public function test_payment_receipt_and_invoice_views_render_properly(): void
    {
        $payment = Payment::first();
        if ($payment) {
            $receiptResponse = $this->actingAs($this->user)->get("/billing/payments/{$payment->id}/receipt");
            $receiptResponse->assertStatus(200);
            $receiptResponse->assertSee($payment->receipt_number);
        }

        $invoice = Invoice::first();
        if ($invoice) {
            $invoiceResponse = $this->actingAs($this->user)->get("/billing/invoices/{$invoice->id}/print");
            $invoiceResponse->assertStatus(200);
            $invoiceResponse->assertSee($invoice->invoice_number);
        }
    }

    public function test_refund_operation_updates_payment_status_and_balances(): void
    {
        // 1. Create a payment first
        $this->actingAs($this->user)->post('/billing/payments/record', [
            'customer_id' => $this->customer->id,
            'application_id' => $this->application->id,
            'amount' => 50.00,
            'payment_mode' => 'CASH',
            'payment_date' => now()->toDateString(),
            'notes' => 'Cash received',
        ]);

        $payment = Payment::where('customer_id', $this->customer->id)->latest('id')->first();

        // 2. Process refund
        $refundResponse = $this->actingAs($this->user)->post("/billing/payments/{$payment->id}/refund", [
            'refund_amount' => 50.00,
            'reason' => 'Customer requested cancellation',
        ]);

        $refundResponse->assertRedirect();

        $payment->refresh();
        $this->assertEquals('REFUNDED', $payment->payment_status);

        $this->assertDatabaseHas('payment_refunds', [
            'payment_id' => $payment->id,
            'refund_amount' => 50.00,
            'reason' => 'Customer requested cancellation',
        ]);
    }
}
