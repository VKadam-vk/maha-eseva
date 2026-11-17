<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('sub_service', 150)->nullable()->after('service_id');
            $table->date('next_follow_up_date')->nullable()->after('status');
            $table->string('next_follow_up_time', 20)->nullable()->after('next_follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['sub_service', 'next_follow_up_date', 'next_follow_up_time']);
        });
    }
};

