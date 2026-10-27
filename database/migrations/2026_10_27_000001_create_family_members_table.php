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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('name');
            $table->string('relation', 50)->default('OTHER'); // SON, DAUGHTER, WIFE, HUSBAND, FATHER, MOTHER, BROTHER, SISTER, OTHER
            $table->string('gender', 20)->default('MALE'); // MALE, FEMALE, OTHER
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('family_member_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('family_members')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['family_member_id']);
            $table->dropColumn('family_member_id');
        });

        Schema::dropIfExists('family_members');
    }
};
