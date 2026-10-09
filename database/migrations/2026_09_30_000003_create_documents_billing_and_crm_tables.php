<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Documents (Private Document Vault)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->cascadeOnDelete();
            
            $table->string('document_type_name'); // e.g. "Aadhaar Card", "Ration Card", "Passport Photo"
            $table->string('original_filename');
            $table->string('storage_path'); // private storage path
            $table->string('disk')->default('local');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('file_hash', 64)->nullable(); // SHA-256 for integrity & malware check
            
            // Document Status
            $table->string('status')->default('RECEIVED'); // REQUIRED, RECEIVED, VERIFIED, REJECTED, NOT_APPLICABLE
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'application_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 2. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            
            $table->string('invoice_number', 40); // e.g. INV-2026-00001
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            
            $table->string('status')->default('UNPAID'); // UNPAID, PARTIAL, PAID, REFUNDED
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'invoice_date']);
        });

        // 3. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('description');
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. Payments (Receipts)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            
            $table->string('receipt_number', 40); // e.g. REC-2026-00001
            $table->date('payment_date');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('payment_mode')->default('CASH'); // CASH, UPI, BANK_TRANSFER, CARD, CHEQUE, WALLET
            $table->string('transaction_reference')->nullable(); // UPI UTR or Bank Ref
            $table->string('payment_status')->default('SUCCESS'); // SUCCESS, PENDING, FAILED, REFUNDED
            $table->text('notes')->nullable();
            
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'receipt_number']);
            $table->index(['tenant_id', 'branch_id', 'payment_date']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'payment_mode']);
        });

        // 5. Payment Refunds
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->decimal('refund_amount', 10, 2);
            $table->string('refund_mode')->default('CASH');
            $table->text('reason')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Employee Tasks
        Schema::create('employee_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('MEDIUM'); // LOW, MEDIUM, HIGH, URGENT
            $table->string('status')->default('PENDING'); // PENDING, IN_PROGRESS, COMPLETED, CANCELLED
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
        });

        // 7. Follow-ups
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->date('follow_up_date');
            $table->time('follow_up_time')->nullable();
            $table->string('reason'); // Document Collection, Payment Due, Status Intimation, Enquiry Followup
            $table->text('remarks')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, MISSED, CANCELLED
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'follow_up_date', 'status']);
            $table->index(['tenant_id', 'assigned_employee_id']);
            $table->index(['tenant_id', 'customer_id']);
        });

        // 8. Leads / Website Enquiries
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('message')->nullable();
            $table->string('source')->default('WEBSITE'); // WEBSITE, WALK_IN, PHONE, WHATSAPP, REFERRAL
            $table->string('status')->default('NEW'); // NEW, CONTACTED, FOLLOW_UP, QUALIFIED, CONVERTED, LOST
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('converted_application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'mobile']);
        });

        // 9. Notifications Log
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('type'); // APPLICATION_CREATED, STATUS_CHANGED, PAYMENT_RECEIVED, FOLLOW_UP_DUE, OTP
            $table->string('channel')->default('SYSTEM'); // SYSTEM, SMS, WHATSAPP, EMAIL
            $table->string('recipient');
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('SENT'); // PENDING, SENT, FAILED
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });

        // 10. Audit Logs (Append-Only)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 50); // LOGIN, LOGOUT, FAILED_LOGIN, CREATE, UPDATE, DELETE, STATUS_CHANGE, EXPORT, DOCUMENT_DOWNLOAD
            $table->string('entity_type', 100)->nullable(); // App\Models\Customer, App\Models\Application, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'event']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        // 11. OTP Verifications (Public Customer Tracking Portal)
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('mobile', 20);
            $table->string('application_number', 40);
            $table->string('otp_hash');
            $table->string('tracking_token', 64)->nullable()->unique();
            $table->timestamp('expires_at');
            $table->unsignedInteger('attempts')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['mobile', 'application_number']);
            $table->index('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('employee_tasks');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('documents');
    }
};
