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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('alternate_mobile');
            }
            if (!Schema::hasColumn('customers', 'star_rating')) {
                $table->unsignedTinyInteger('star_rating')->default(5)->after('occupation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'occupation')) {
                $table->dropColumn('occupation');
            }
            if (Schema::hasColumn('customers', 'star_rating')) {
                $table->dropColumn('star_rating');
            }
        });
    }
};
