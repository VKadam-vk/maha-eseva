<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Services\BillingService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Payments / Collections Ledger.
     */
    public function payments(Request $request): View
    {
        $user = Auth::user();
        $query = Payment::with(['customer', 'application.service', 'receiver', 'branch']);

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('receipt_number', 'like', "%{$s}%")
                  ->orWhere('transaction_reference', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($c) use ($s) {
                      $c->where('name', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%");
                  });
            });
        }

        $payments = $query->latest('payment_date')->latest('id')->paginate(15)->withQueryString();
        $totalCollected = (float) Payment::where('payment_status', 'SUCCESS')->sum('amount');
        $todayCollected = (float) Payment::where('payment_status', 'SUCCESS')->whereDate('payment_date', now()->toDateString())->sum('amount');

        return view('billing.payments', compact('payments', 'totalCollected', 'todayCollected'));
    }

    /**
     * Record a new payment.
     */
    public function recordPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'application_id' => ['nullable', 'exists:applications,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', 'in:CASH,UPI,BANK_TRANSFER,CARD,CHEQUE,WALLET'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $this->billingService->recordPayment($validated, Auth::user());
        
        $customer = Customer::find($validated['customer_id']);
        if ($customer) {
            $this->notificationService->notifyPaymentReceived($customer->tenant_id, $customer, $payment->amount, $payment->receipt_number);
        }

        return back()->with('success', "Payment of Rs. " . number_format($payment->amount, 2) . " recorded. Receipt #{$payment->receipt_number}.");
    }

    /**
     * Invoices listing.
     */
    public function invoices(Request $request): View
    {
        $query = Invoice::with(['customer', 'application', 'branch', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();
        return view('billing.invoices', compact('invoices'));
    }

    /**
     * Show create invoice form.
     */
    public function createInvoice(): View
    {
        $customers = Customer::latest()->limit(50)->get();
        $services = Service::where('is_active', true)->get();
        return view('billing.create_invoice', compact('customers', 'services'));
    }

    /**
     * Store new invoice.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'application_id' => ['nullable', 'exists:applications,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.tax_percentage' => ['nullable', 'numeric', 'min:0'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
        ]);

        $invoice = $this->billingService->createInvoice($validated, $validated['items'], Auth::user());

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    /**
     * Show invoice details.
     */
    public function showInvoice(Invoice $invoice): View
    {
        $invoice->load(['customer', 'application.service', 'items', 'payments.receiver', 'branch.tenant']);
        return view('billing.show_invoice', compact('invoice'));
    }

    /**
     * Print printable invoice format.
     */
    public function printInvoice(Invoice $invoice): View
    {
        $invoice->load(['customer', 'application.service', 'items', 'payments', 'branch.tenant']);
        return view('billing.print_invoice', compact('invoice'));
    }

    /**
     * Print printable payment receipt.
     */
    public function printReceipt(Payment $payment): View
    {
        $payment->load(['customer', 'application.service', 'receiver', 'branch.tenant']);
        return view('billing.print_receipt', compact('payment'));
    }

    /**
     * Process payment refund.
     */
    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('refund', $payment);

        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', "max:{$payment->amount}"],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->billingService->processRefund($payment, $validated['refund_amount'], $validated['reason'], Auth::user());

        return back()->with('success', "Refund of Rs. " . number_format($validated['refund_amount'], 2) . " processed successfully.");
    }
}
