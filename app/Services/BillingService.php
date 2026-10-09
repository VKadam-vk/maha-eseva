<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate unique invoice number.
     */
    public function generateInvoiceNumber(int $tenantId): string
    {
        $year = date('Y');
        $count = Invoice::where('tenant_id', $tenantId)->count() + 1;
        $num = sprintf('INV-%s-%05d', $year, $count);

        while (Invoice::where('tenant_id', $tenantId)->where('invoice_number', $num)->exists()) {
            $count++;
            $num = sprintf('INV-%s-%05d', $year, $count);
        }

        return $num;
    }

    /**
     * Generate unique receipt number.
     */
    public function generateReceiptNumber(int $tenantId): string
    {
        $year = date('Y');
        $count = Payment::where('tenant_id', $tenantId)->count() + 1;
        $num = sprintf('REC-%s-%05d', $year, $count);

        while (Payment::where('tenant_id', $tenantId)->where('receipt_number', $num)->exists()) {
            $count++;
            $num = sprintf('REC-%s-%05d', $year, $count);
        }

        return $num;
    }

    /**
     * Record a payment and update application/invoice balances atomically.
     */
    public function recordPayment(array $data, User $receiver): Payment
    {
        $tenantId = $receiver->tenant_id;
        $branchId = $data['branch_id'] ?? $receiver->branch_id ?? $receiver->tenant->branches()->first()?->id;
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($data, $amount, $tenantId, $branchId, $receiver) {
            $receiptNumber = $this->generateReceiptNumber($tenantId);

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'application_id' => $data['application_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'receipt_number' => $receiptNumber,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'] ?? Payment::MODE_CASH,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_status' => Payment::STATUS_SUCCESS,
                'notes' => $data['notes'] ?? null,
                'received_by' => $receiver->id,
            ]);

            // If attached to application, update application financials
            if (!empty($data['application_id'])) {
                $application = Application::lockForUpdate()->find($data['application_id']);
                if ($application) {
                    $application->recalculateFinancials();
                }
            }

            // If attached to invoice, update invoice paid amount
            if (!empty($data['invoice_id'])) {
                $invoice = Invoice::lockForUpdate()->find($data['invoice_id']);
                if ($invoice) {
                    $paid = (float) $invoice->payments()->where('payment_status', 'SUCCESS')->sum('amount');
                    $invoice->paid_amount = $paid;
                    $invoice->balance_amount = max(0, $invoice->total_amount - $paid);
                    $invoice->status = ($invoice->balance_amount <= 0) ? Invoice::STATUS_PAID : ($paid > 0 ? Invoice::STATUS_PARTIAL : Invoice::STATUS_UNPAID);
                    $invoice->save();
                }
            }

            AuditService::log('PAYMENT_CREATED', $payment, null, $payment->toArray());

            return $payment;
        });
    }

    /**
     * Process a payment refund with database transaction.
     */
    public function processRefund(Payment $payment, float $refundAmount, string $reason, User $refunder): PaymentRefund
    {
        if ($refundAmount <= 0 || $refundAmount > $payment->amount) {
            throw new \InvalidArgumentException('Invalid refund amount.');
        }

        return DB::transaction(function () use ($payment, $refundAmount, $reason, $refunder) {
            $refund = PaymentRefund::create([
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
                'refund_mode' => $payment->payment_mode,
                'reason' => $reason,
                'refunded_by' => $refunder->id,
            ]);

            $payment->update([
                'payment_status' => Payment::STATUS_REFUNDED,
            ]);

            if ($payment->application) {
                $payment->application->recalculateFinancials();
            }

            AuditService::log('PAYMENT_REFUNDED', $payment, ['payment_id' => $payment->id], [
                'refund_amount' => $refundAmount,
                'reason' => $reason,
            ]);

            return $refund;
        });
    }

    /**
     * Generate an invoice for an application or customer.
     */
    public function createInvoice(array $data, array $items, User $creator): Invoice
    {
        $tenantId = $creator->tenant_id;
        $branchId = $data['branch_id'] ?? $creator->branch_id ?? $creator->tenant->branches()->first()?->id;

        return DB::transaction(function () use ($data, $items, $tenantId, $branchId, $creator) {
            $invoiceNumber = $this->generateInvoiceNumber($tenantId);

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxPct = (float) ($item['tax_percentage'] ?? 0);
                $itemSubtotal = $unitPrice * $qty;
                $itemTax = ($itemSubtotal * $taxPct) / 100;
                $subtotal += $itemSubtotal;
                $taxTotal += $itemTax;
            }

            $discount = (float) ($data['discount_amount'] ?? 0);
            $total = max(0, ($subtotal + $taxTotal) - $discount);

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'application_id' => $data['application_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'paid_amount' => 0.00,
                'balance_amount' => $total,
                'status' => Invoice::STATUS_UNPAID,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
            ]);

            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxPct = (float) ($item['tax_percentage'] ?? 0);
                $lineTotal = ($unitPrice * $qty) + (($unitPrice * $qty * $taxPct) / 100);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'tax_percentage' => $taxPct,
                    'total_price' => $lineTotal,
                ]);
            }

            AuditService::log('INVOICE_CREATED', $invoice, null, $invoice->toArray());

            return $invoice;
        });
    }
}
