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
        Schema::create('application_field_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_name', 50); // work_status, assigned_employee_id, follow_up_date, rate, sub_service
            $table->string('field_label', 100); // Status, Operator, Follow-Up Date, Rate, Sub Service
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('old_display')->nullable();
            $table->text('new_display')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['application_id', 'created_at']);
            $table->index(['application_id', 'field_name']);
            $table->index(['tenant_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_field_histories');
    }
};
