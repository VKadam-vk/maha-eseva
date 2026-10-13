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
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'sub_service')) {
                $table->string('sub_service', 100)->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('applications', 'documents_list')) {
                $table->text('documents_list')->nullable()->after('work_details');
            }
            if (!Schema::hasColumn('applications', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'sub_service')) {
                $table->dropColumn('sub_service');
            }
            if (Schema::hasColumn('applications', 'documents_list')) {
                $table->dropColumn('documents_list');
            }
            if (Schema::hasColumn('applications', 'follow_up_date')) {
                $table->dropColumn('follow_up_date');
            }
        });
    }
};
