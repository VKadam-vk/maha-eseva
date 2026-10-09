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
        // 1. Customer Categories
        Schema::create('customer_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // Regular, Senior Citizen, Student, BPL, VIP
            $table->string('code', 20)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        // 2. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('customer_code', 30);
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('gender')->default('MALE'); // MALE, FEMALE, OTHER
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('Maharashtra');
            $table->string('pincode', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('alternate_mobile', 20)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('customer_categories')->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'customer_code']);
            $table->index(['tenant_id', 'mobile']);
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['tenant_id', 'name']);
        });

        // 3. Service Categories
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // Identity, Revenue & Land, Police & Legal, Business, Welfare
            $table->string('slug');
            $table->string('icon')->default('folder');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'slug']);
        });

        // 4. Services Master
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('service_code', 30);
            $table->string('main_service_name'); // e.g. PAN CARD
            $table->string('sub_service_name'); // e.g. New PAN 49A
            $table->string('service_variant')->nullable(); // Physical + ePAN, Minor, etc.
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00); // base price
            $table->decimal('govt_fee', 10, 2)->default(0.00);
            $table->decimal('service_charge', 10, 2)->default(0.00);
            $table->json('additional_charge_rules_json')->nullable();
            $table->json('required_documents_json')->nullable(); // ["Aadhaar Card", "Passport Photo", "Signature"]
            $table->string('service_portal_link')->nullable(); // Official gov site URL
            $table->string('work_portal_link')->nullable(); // Operator work login URL
            $table->string('video_instruction_link')->nullable(); // YouTube or guidance video
            $table->text('portal_username_encrypted')->nullable(); // Encrypted at app level
            $table->text('portal_password_encrypted')->nullable(); // Encrypted at app level
            $table->unsignedInteger('expected_processing_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'service_code']);
            $table->index(['tenant_id', 'is_active']);
        });

        // 5. Service Custom Fields
        Schema::create('service_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_key', 50); // e.g. aadhaar_number, father_name
            $table->string('field_type'); // text, textarea, number, date, datetime, email, mobile, select, multiselect, radio, checkbox, file, password, url
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options_json')->nullable(); // For select, radio, multiselect
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules')->nullable(); // e.g. "digits:12"
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_id', 'is_active']);
        });

        // 6. Applications (Service Requests)
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('application_number', 40); // Unique SR e.g., SR-2026-00001
            $table->date('application_date');
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            // Work Status State Machine
            $table->string('work_status')->default('NEW'); 
            // Baseline statuses: NEW, DOCUMENT_PENDING, DOCUMENT_VERIFIED, IN_PROCESS, SUBMITTED, UNDER_PROCESS, RETURNED_CORRECTION, APPROVED, READY, DELIVERED, CLOSED, ON_HOLD, REJECTED, CANCELLED
            
            // Payment Status
            $table->string('payment_status')->default('PENDING'); // PENDING, PARTIAL, PAID, REFUNDED
            
            // Financials
            $table->decimal('base_amount', 10, 2)->default(0.00);
            $table->decimal('govt_fee', 10, 2)->default(0.00);
            $table->decimal('service_charge', 10, 2)->default(0.00);
            $table->decimal('additional_charges', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('received_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_amount', 10, 2)->default(0.00);
            
            // External references
            $table->string('external_acknowledgement_no')->nullable(); // Government Ack / Token / Application ID
            $table->string('external_portal_login_user')->nullable();
            
            // Dates & Tracking
            $table->date('submit_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            
            // Delivery
            $table->string('delivery_status')->default('PENDING'); // PENDING, READY_FOR_PICKUP, DELIVERED_PHYSICAL, DELIVERED_DIGITAL
            $table->timestamp('delivery_date')->nullable();
            
            // Remarks & Details
            $table->text('work_details')->nullable();
            $table->text('pending_remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'application_number']);
            $table->index(['tenant_id', 'branch_id', 'work_status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'assigned_employee_id']);
            $table->index(['tenant_id', 'payment_status']);
            $table->index(['tenant_id', 'application_date']);
        });

        // 7. Application Custom Values
        Schema::create('application_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('service_custom_fields')->cascadeOnDelete();
            $table->string('field_key', 50);
            $table->text('field_value')->nullable(); // Encrypted at application level if sensitive
            $table->timestamps();

            $table->index(['application_id', 'field_key']);
        });

        // 8. Application Status Histories
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['application_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('application_custom_values');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('service_custom_fields');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_categories');
    }
};
