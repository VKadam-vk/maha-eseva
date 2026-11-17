<?php

use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. If any lead has converted_customer_id, ensure customer branch_id matches lead branch_id
        $leads = Lead::whereNotNull('converted_customer_id')->get();
        foreach ($leads as $lead) {
            $branchId = $lead->branch_id ?: 2; // Default to active branch (Shravani E-Seva Kendra) if unset
            if ($lead->converted_customer_id) {
                DB::table('customers')
                    ->where('id', $lead->converted_customer_id)
                    ->update(['branch_id' => $branchId]);

                if ($lead->converted_application_id) {
                    DB::table('applications')
                        ->where('id', $lead->converted_application_id)
                        ->update(['branch_id' => $branchId]);
                }
            }
        }

        // 2. Fix customer 85 specifically (seen in screenshot) so it's directly accessible
        $lead85 = Lead::where('converted_customer_id', 85)->first();
        if ($lead85) {
            $branchId = $lead85->branch_id ?: 2;
            DB::table('customers')->where('id', 85)->update(['branch_id' => $branchId]);
            DB::table('leads')->where('id', $lead85->id)->update(['branch_id' => $branchId]);
        } else {
            // If customer 85 exists in customers table, assign branch_id = 2
            DB::table('customers')->where('id', 85)->whereNull('branch_id')->orWhere('branch_id', 1)->where('id', 85)->update(['branch_id' => 2]);
        }
    }

    public function down(): void
    {
        // No rollback necessary
    }
};
