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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('branch_code');
            $table->string('pan_number', 20)->nullable()->after('owner_name');
            $table->string('aadhaar_number', 20)->nullable()->after('pan_number');
            $table->string('shop_licence_number', 50)->nullable()->after('aadhaar_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['owner_name', 'pan_number', 'aadhaar_number', 'shop_licence_number']);
        });
    }
};
