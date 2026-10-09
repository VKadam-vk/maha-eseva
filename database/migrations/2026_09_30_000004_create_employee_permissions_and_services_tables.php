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
        // 1. Direct User Permissions Pivot (Granular employee permissions)
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['user_id', 'permission_id']);
            });
        }

        // 2. Employee Services Pivot (Allowed E-Seva services per employee)
        if (!Schema::hasTable('employee_services')) {
            Schema::create('employee_services', function (Blueprint $table) {
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->primary(['employee_id', 'service_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_services');
        Schema::dropIfExists('user_permissions');
    }
};
