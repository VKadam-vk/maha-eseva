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
        // 1. Tenants (Business Organizations)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('business_type')->default('MAHA_E_SEVA'); // E-SEVA, CSC, GRAHAK_SEVA, MULTI_SERVICES
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_mobile');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('Maharashtra');
            $table->string('pincode', 10)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, SUSPENDED, TRIAL, EXPIRED
            $table->string('subscription_plan')->default('PREMIUM');
            $table->date('subscription_expires_at')->nullable();
            $table->unsignedInteger('max_branches')->default(5);
            $table->unsignedInteger('max_users')->default(25);
            $table->unsignedInteger('max_storage_mb')->default(5120); // 5GB
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('contact_mobile');
        });

        // 2. Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('branch_code', 30);
            $table->string('contact_person')->nullable();
            $table->string('contact_mobile', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->default('Maharashtra');
            $table->string('pincode', 10)->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, INACTIVE
            $table->boolean('is_main_branch')->default(false);
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'branch_code']);
            $table->index(['tenant_id', 'status']);
        });

        // 3. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // PLATFORM_SUPER_ADMIN, BUSINESS_OWNER, BRANCH_ADMIN, EMPLOYEE, CUSTOMER
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        // 4. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // customers.view, applications.create, etc.
            $table->string('module'); // customers, applications, documents, payments, etc.
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('module');
        });

        // 5. Role Permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 6. Update/Alter Users Table to add Tenant & Branch & Role fields
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('tenant_id')->constrained('branches')->nullOnDelete();
            $table->string('mobile', 20)->nullable()->after('email');
            $table->string('status')->default('ACTIVE')->after('password'); // ACTIVE, INACTIVE, LOCKED
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('status');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('last_login_at')->nullable()->after('locked_until');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('avatar_path')->nullable()->after('last_login_ip');
            $table->text('two_factor_secret')->nullable()->after('avatar_path');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->softDeletes()->after('updated_at');

            $table->index(['tenant_id', 'branch_id']);
            $table->index('status');
            $table->index('mobile');
        });

        // 7. User Roles Pivot
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // 8. Employees Master
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_code', 30);
            $table->string('designation')->default('Service Operator');
            $table->date('joining_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0.00);
            $table->string('id_proof_type')->nullable(); // AADHAR, PAN, VOTER
            $table->string('id_proof_number')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, INACTIVE, ON_LEAVE
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'employee_code']);
            $table->index(['tenant_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('user_roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'tenant_id', 'branch_id', 'mobile', 'status', 
                'failed_login_attempts', 'locked_until', 'last_login_at', 
                'last_login_ip', 'avatar_path', 'two_factor_secret', 
                'two_factor_enabled', 'deleted_at'
            ]);
        });
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('tenants');
    }
};
